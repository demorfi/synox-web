<?php declare(strict_types=1);

namespace App\Components\Packages;

use App\Package\Extension\{Abstracts\Package, Enums\Subtype};
use App\Package\Search\Relay;
use App\Package\Search\Interfaces\{Item as PackageItemInterface, Content as PackageContentInterface};
use App\Components\Storage\{Journal, Elastic};
use Digua\{Env, LateEvent};
use Digua\Components\{Event, Storage};
use Digua\Exceptions\Base as BaseException;

class Indexer extends Package
{
    /**
     * @var Subtype
     */
    private Subtype $subtype = Subtype::BASE;

    /**
     * @var string
     */
    private string $name = 'Indexer';

    /**
     * @var string
     */
    private string $description = 'Indexing all search queries';

    /**
     * @var string
     */
    private string $version = '1.0';

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
            if (Env::get('ELASTIC_USE') && class_exists('Elasticsearch\Client')) {
                Storage::make(Elastic::class, '');
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
        return ['Elasticsearch'];
    }

    /**
     * @inheritdoc
     */
    public function wakeup(): void
    {
        LateEvent::subscribe(Relay::class . '::searchReturn', $this->eventSearchReturn(...));
        LateEvent::subscribe(Relay::class . '::fetchReturn', $this->eventFetchReturn(...));
    }

    /**
     * @return string
     */
    private function getStorageType(): string
    {
        return $this->version . '-' . 'eb-indexer';
    }

    /**
     * @param Event  $event
     * @param string $fetchId
     * @return string
     */
    private function getStorageId(Event $event, string $fetchId): string
    {
        return md5(strtok($event->getId(), ':') . '-' . $fetchId);
    }

    /**
     * @param Event $event
     * @return void
     */
    private function eventSearchReturn(Event $event): void
    {
        $event->addHandler(function (Event $event, mixed $previous, PackageItemInterface $item) {
            try {
                if (str_starts_with($event->getId(), 'search@indexersearch')
                    || $item->getProperty('Cached') !== null) {
                    return null;
                }

                /* @var Elastic $storage */
                $storage   = Storage::make(Elastic::class, $this->getStorageType());
                $storageId = $this->getStorageId($event, $item->getFetchId());
                if ($storage->hasId($storageId)) {
                    $storage->update($storageId, $item->jsonSerialize());
                } else {
                    $storage->write(['id' => $this->getStorageId($event, $item->getFetchId()), 'body' => $item->jsonSerialize()]);
                }
            } catch (BaseException $e) {
                Journal::staticPush(sprintf('Indexer Write Error: %s', $e->getMessage()));
            }
        });
    }

    /**
     * @param Event $event
     * @return void
     */
    private function eventFetchReturn(Event $event): void
    {
        $event->addHandler(function (Event $event, mixed $previous, ?PackageContentInterface $content) {
            if ($content instanceof PackageContentInterface) {
                try {
                    /* @var Elastic $storage */
                    $storage   = Storage::make(Elastic::class, $this->getStorageType());
                    $storageId = $this->getStorageId($event, $event->query->value);
                    if ($storage->hasId($storageId)) {
                        $storage->update($storageId, ['content' => $content->jsonSerialize()]);
                    }
                } catch (BaseException $e) {
                    Journal::staticPush(sprintf('Indexer Write Error: %s', $e->getMessage()));
                }
            }
        });
    }
}