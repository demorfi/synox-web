<?php declare(strict_types=1);

namespace App\Package\Search\Item;

class Torrent extends Text
{
    /**
     * @var int|string
     */
    protected int|string $seeds = '?';

    /**
     * @var int|string
     */
    protected int|string $peers = '?';

    /**
     * @return int|string
     */
    public function getSeeds(): int|string
    {
        return $this->seeds;
    }

    /**
     * @param int $seeds
     * @return void
     */
    public function setSeeds(int $seeds): void
    {
        $this->seeds = $seeds;
    }

    /**
     * @return int|string
     */
    public function getPeers(): int|string
    {
        return $this->peers;
    }

    /**
     * @param int $peers
     * @return void
     */
    public function setPeers(int $peers): void
    {
        $this->peers = $peers;
    }
}