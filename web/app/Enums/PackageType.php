<?php declare(strict_types=1);

namespace App\Enums;

use App\Interfaces\{
    Download as DownloadInterface,
    Lyrics as LyricInterface
};

enum PackageType: string
{
    case Download = DownloadInterface::class;

    case Lyrics = LyricInterface::class;

    /**
     * @return string
     */
    public function getInterface(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return strtolower($this->name);
    }
}
