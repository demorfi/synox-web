<?php declare(strict_types=1);

namespace App\Package\Search;

use App\Package\{Abstracts\Relay as RelayAbstract, Enums\Type as PackageType, Search\Interfaces\Content};
use Digua\{LateEvent, Components\Event};

final class Relay extends RelayAbstract
{
    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        return [
            ...parent::jsonSerialize(),
            'usesAuth'    => $this->package->hasAuth(),
            'onlyAllowed' => $this->package->onlyAllowed()
        ];
    }

    /**
     * @inheritdoc
     */
    public function getType(): PackageType
    {
        return PackageType::SEARCH;
    }

    /**
     * @param Query $query
     * @return Event
     */
    private function newEvent(Query $query): Event
    {
        return new Event(
            ['query' => $query, 'package' => $this->package],
            sprintf('%s:%s', $this->getId(), $query->value)
        );
    }

    /**
     * @param Event  $event
     * @param string $name
     * @param mixed  $data
     * @return void
     */
    private function voidEvent(Event $event, string $name, mixed $data): void
    {
        LateEvent::notify(self::class . '::' . $name, $event);
        $event($data);
    }

    /**
     * @inheritdoc
     */
    public function search(Query $query): iterable
    {
        $event = $this->newEvent($query);
        LateEvent::notify(__METHOD__, $event);

        $event->addHandler(function (Event $event, mixed $previous) {
            return is_iterable($previous) && $previous->valid() ? $previous : $this->result($event->query);
        });

        foreach ($event() as $result) {
            if (is_a($result, $this->package->getSubtype()->value)) {
                $this->voidEvent($this->newEvent($query), 'searchReturn', $result);
                yield $result;
            }
        }
    }

    /**
     * @param Query $query
     * @return iterable
     */
    private function result(Query $query): iterable
    {
        $event = $this->newEvent($query);
        LateEvent::notify(__METHOD__, $event);

        $event->addHandler(function (Event $event, mixed $previous, mixed $result) {
            return $previous ?? $result;
        });

        foreach ($this->package->search($event->query) as $result) {
            yield $event($result);
        }
    }

    /**
     * @inheritdoc
     */
    public function fetch(Query $query): ?Content
    {
        $event = $this->newEvent($query);
        LateEvent::notify(__METHOD__, $event);

        $event->addHandler(function (Event $event, mixed $previous) {
            return $previous ?? $this->fetched($event->query);
        });

        $result = $event();
        if (is_a($result, $this->package->getSubtype()->content())) {
            $this->voidEvent($this->newEvent($query), 'fetchReturn', $result);
            return $result;
        }

        return null;
    }

    /**
     * @param Query $query
     * @return mixed
     */
    private function fetched(Query $query): mixed
    {
        $event = $this->newEvent($query);
        LateEvent::notify(__METHOD__, $event);

        $event->addHandler(function (Event $event, mixed $previous, mixed $result) {
            return $previous ?? $result;
        });

        $content = $this->package->fetch($event->query);
        return $event($content);
    }
}