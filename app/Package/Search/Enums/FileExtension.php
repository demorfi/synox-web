<?php declare(strict_types=1);

namespace App\Package\Search\Enums;

enum FileExtension: string
{
    case TORRENT = '.torrent';

    case TEXT = '.txt';
}