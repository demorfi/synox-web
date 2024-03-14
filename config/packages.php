<?php declare(strict_types=1);

use Digua\Env;

$search = [
    'NnmClub',
    'FastTorrent',
    'RuTracker',
    'Tpb',
    'Kinozal',
    'Rutor',
    'Byrutor',
    'Pornolab',
    'SongLyrics',
    'Bananan',
    'AllofLyrics',
    'Jackett'
];

if (Env::isDev()) {
    $search = [...$search, 'TestTorrent', 'TestText'];
}

$extension = [
    'Cache',
    'JackettApiHook'
];

return compact('search', 'extension');