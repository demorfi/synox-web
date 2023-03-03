<?php declare(strict_types=1);

namespace App\Components\Packages\Lyrics;

use App\Abstracts\Package;
use App\Interfaces\Lyrics as LyricsInterface;
use App\Package\PackageQuery;
use App\Package\Lyrics\{Content, Item};
use Generator;

class TestLyrics extends Package implements LyricsInterface
{
    /**
     * @var string
     */
    private string $name = 'Test Lyrics';

    /**
     * @var string
     */
    private string $shortDescription = 'Test lyrics';

    /**
     * @var string
     */
    private string $version = '1.0';

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    /**
     * @inheritdoc
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @inheritdoc
     */
    public function hasAuth(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function search(PackageQuery $query): Generator
    {
        $maxResults = rand(6, 24);
        for ($i = 1; $i <= $maxResults; $i++) {
            $item = new Item($this);
            $item->setTitle($i . ' Test ' . $query->value);
            $item->setArtist('Test artist ' . $i);
            $item->setContent('Test lyrics short ' . $i);
            yield $item;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function fetch(string $url, Content $content): bool
    {
        $content->add('test ' . PHP_EOL . ' content');
        return $content->isAvailable();
    }
}
