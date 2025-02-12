<?php declare(strict_types=1);

namespace App\Components\Packages;

use App\Package\Extension\{Abstracts\Package, Enums\Subtype};
use App\Package\Search\Relay;
use App\Package\Search\Interfaces\{Item as PackageItemInterface, Content as PackageContentInterface};
use App\Components\Storage\{Journal, RedisLists};
use Digua\LateEvent;
use Digua\Components\{Event, Storage};
use Digua\Exceptions\Base as BaseException;

class Cache extends Package
{
    /**
     * @var Subtype
     */
    private Subtype $subtype = Subtype::BASE;

    /**
     * @var string
     */
    private string $name = 'Cache';

    /**
     * @var string
     */
    private string $description = 'Caching search queries';

    /**
     * @var string
     */
    private string $version = '1.1';

    /**
     * @inheritdoc
     */
    public function getSubtype(): Subtype
    {
        return $this->subtype;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        try {
            if (class_exists('Redis')) {
                Storage::make(RedisLists::class, '');
                return true;
            }
        } catch (BaseException) {
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getRequires(): array
    {
        return ['Redis'];
    }

    /**
     * @inheritdoc
     */
    public function wakeup(): void
    {
        LateEvent::subscribe(Relay::class . '::search', $this->eventSearch(...));
        LateEvent::subscribe(Relay::class . '::result', $this->eventResult(...));
        LateEvent::subscribe(Relay::class . '::fetch', $this->eventFetch(...));
        LateEvent::subscribe(Relay::class . '::fetched', $this->eventFetched(...));
    }

    /**
     * @param Event $event
     * @return string
     */
    private function getStorageId(Event $event): string
    {
        return $this->version . '-' . md5($event->getId());
    }

    /**
     * @param Event $event
     * @return void
     */
    private function eventSearch(Event $event): void
    {
        $event->addHandler(function (Event $event) {
            try {
                $storage = Storage::make(RedisLists::class, $this->getStorageId($event));
                if (is_array($result = $storage->read())) {
                    foreach ($result as $data) {
                        $item = unserialize($data);
                        if ($item instanceof PackageItemInterface) {
                            $item->addProperty('Cached', 'Yes');

                            $fStorage = Storage::make(
                                RedisLists::class,
                                $this->getStorageId(
                                    Event::make(id: sprintf('%s:%s', $item->getId(), $item->getFetchId()))
                                )
                            );

                            if (is_array($content = $fStorage->read())) {
                                $content = unserialize(array_pop($content));
                                if ($content instanceof PackageContentInterface) {
                                    $item->setContent($content);
                                }
                            }

                            yield $item;
                        }
                    }
                }
            } catch (BaseException $e) {
                Journal::staticPush(sprintf('Cache Read Error: %s', $e->getMessage()));
            }
        });
    }

    /**
     * @param Event $event
     * @return void
     */
    private function eventResult(Event $event): void
    {
        $event->addHandler(function (Event $event, mixed $previous, PackageItemInterface $item) {
            try {
                $storage = Storage::make(RedisLists::class, $this->getStorageId($event));
                $storage->write(serialize($item));
            } catch (BaseException $e) {
                Journal::staticPush(sprintf('Cache Write Error: %s', $e->getMessage()));
            }
        });
    }

    /**
     * @param Event $event
     * @return void
     */
    private function eventFetch(Event $event): void
    {
        $event->addHandler(function (Event $event) {
            try {
                $storage = Storage::make(RedisLists::class, $this->getStorageId($event));
                if (is_array($result = $storage->read())) {
                    $content = unserialize(array_pop($result));
                    if ($content instanceof PackageContentInterface) {
                        return $content;
                    }
                }
            } catch (BaseException $e) {
                Journal::staticPush(sprintf('Cache Read Error: %s', $e->getMessage()));
            }
            return null;
        });
    }

    /**
     * @param Event $event
     * @return void
     */
    private function eventFetched(Event $event): void
    {
        $event->addHandler(function (Event $event, mixed $previous, ?PackageContentInterface $content) {
            if ($content instanceof PackageContentInterface) {
                try {
                    $storage = Storage::make(RedisLists::class, $this->getStorageId($event));
                    $storage->write(serialize($content));
                } catch (BaseException $e) {
                    Journal::staticPush(sprintf('Cache Write Error: %s', $e->getMessage()));
                }
            }
        });
    }
}