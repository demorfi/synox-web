<?php declare(strict_types=1);

namespace App\Package\Item;

use App\Abstracts\PackageItem;

class Torrent extends PackageItem
{
    /**
     * @var int
     */
    protected int $seeds = 0;

    /**
     * @var int
     */
    protected int $peers = 0;

    /**
     * @return int
     */
    public function getSeeds(): int
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
     * @return int
     */
    public function getPeers(): int
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