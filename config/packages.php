<?php declare(strict_types=1);

use Digua\Env;

$packages = [
    'NnmClub',
    'FastTorrent',
    'RuTracker',
    'Tpb',
    'Kinozal',
    'Rutor',
    'Pornolab',
    'SongLyrics',
    'Bananan',
    'AllofLyrics'
];

if (Env::isDev()) {
    $packages = [...$packages, 'TestTorrent', 'TestText'];
}

return $packages;
