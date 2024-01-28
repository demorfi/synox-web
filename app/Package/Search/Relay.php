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
     * @inheritdoc
     */
    public function search(Query $query): iterable
    {
        $event = new Event(
            ['query' => $query, 'package' => $this->package],
            sprintf('%s:%s', $this->getId(), $query->value)
        );

        LateEvent::notify(__METHOD__, $event);
        $event->addHandler(function (Event $event, mixed $previous) {
            return is_iterable($previous) && $previous->valid() ? $previous : $this->result($event->query);
        });

        foreach ($event() as $result) {
            yield is_a($result, $this->package->getType()->value) ? $result : null;
        }
    }

    /**
     * @param Query $query
     * @return iterable
     */
    private function result(Query $query): iterable
    {
        $event = new Event(
            ['query' => $query, 'package' => $this->package],
            sprintf('%s:%s', $this->getId(), $query->value)
        );

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
        $event = new Event(
            ['query' => $query, 'package' => $this->package],
            sprintf('%s:%s', $this->getId(), $query->value)
        );

        LateEvent::notify(__METHOD__, $event);
        $event->addHandler(function (Event $event, mixed $previous) {
            return $previous ?? $this->fetched($event->query);
        });

        return $event();
    }

    /**
     * @param Query $query
     * @return ?Content
     */
    private function fetched(Query $query): ?Content
    {
        $event = new Event(
            ['query' => $query, 'package' => $this->package],
            sprintf('%s:%s', $this->getId(), $query->value)
        );

        LateEvent::notify(__METHOD__, $event);
        $event->addHandler(function (Event $event, mixed $previous, mixed $result) {
            return $previous ?? $result;
        });

        $content = $this->package->fetch($event->query);
        return $event($content);
    }
}