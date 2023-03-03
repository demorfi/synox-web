<?php declare(strict_types=1);

namespace App\Package\Download;

use App\Abstracts\PackageItem as PackageItemAbstract;
use DateTime;

class Item extends PackageItemAbstract
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
     * @var string
     */
    protected string $category = 'Unknown category';

    /**
     * @var string
     */
    protected string $date = '-';

    /**
     * @var float
     */
    protected float $size = 0;

    /**
     * @var string
     */
    protected string $weight = '0b';

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

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param string|array $category
     * @return void
     */
    public function setCategory(string|array $category): void
    {
        if (!empty($category)) {
            $this->category = is_array($category)
                ? implode(' / ', array_map(fn($v) => mb_convert_case($v, MB_CASE_TITLE), $category))
                : $category;
        }
    }

    /**
     * Get date.
     *
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param int|DateTime $date
     * @return void
     */
    public function setDate(int|DateTime $date): void
    {
        if (!empty($date)) {
            $this->date = (is_int($date) ? (new DateTime())->setTimestamp($date) : $date)->format('Y-m-d');
        }
    }

    /**
     * @return float
     */
    public function getSize(): float
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getWeight(): string
    {
        return $this->weight;
    }

    /**
     * @param string|float $size
     * @return void
     */
    public function setSize(string|float $size): void
    {
        $unit = ['b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb'];

        // Converting string to byte
        if (is_string($size)) {
            $length = false;
            if (!!preg_match('/^(?P<size>[0-9.]+)\s?(?P<unit>\w+)$/', $size, $matches)) {
                $length = array_search(
                    strlen($matches['unit']) > 1
                        ? ucfirst(strtolower($matches['unit']))
                        : strtolower($matches['unit']),
                    $unit
                );
            }

            $size = $length !== false
                ? round((float)$matches['size'] * pow(1024, $length))
                : 0;
        }

        $length       = (int)floor(log($size, 1024));
        $this->size   = $size;
        $this->weight = round($size / pow(1024, $length), 2) . $unit[$length];
    }
}
