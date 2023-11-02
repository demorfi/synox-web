<?php declare(strict_types=1);

namespace App\Enums;

enum FileExtension: string
{
    case TORRENT = '.torrent';

    case TEXT = '.txt';

    case CONFIG = '.cfg';
}