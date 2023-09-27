<?php declare(strict_types=1);

namespace App\Components\Packages;

use App\Abstracts\Package;
use App\Enums\{ContentType, ItemType, Category};
use App\Interfaces\PackageContent;
use App\Package\{Content\Text as TextContent, Item\Text as TextItem};
use App\Package\Query;
use Generator;

class TestText extends Package
{
    /**
     * @var string
     */
    private string $name = 'Test Text';

    /**
     * @var string
     */
    private string $shortDescription = 'Test search text';

    /**
     * @var string
     */
    private string $version = '1.0';

    /**
     * @inheritdoc
     */
    public function getItemType(): ItemType
    {
        return ItemType::TEXT;
    }

    /**
     * @inheritdoc
     */
    public function getContentType(): ContentType
    {
        return ContentType::TEXT;
    }

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
    public function search(Query $query): Generator
    {
        $maxResults = rand(6, 24);
        for ($i = 1; $i <= $maxResults; $i++) {
            $item = new TextItem($this);
            $item->setCategory(Category::TEXT);
            $item->setTitle($i . ' Test ' . $query->value);
            $item->addProperty('Info-1', 'Text 1 property ' . $i);
            $item->addProperty('Info-2', 'Text 2 property ' . $i);
            $item->setContent('Test short content ' . $i);
            $item->setSize(rand(1000000, 9999999));
            $item->setDate(date_create());
            $item->setFetchId('#fetch/?id=' . $this->name . '&fetch=' . $i);
            $item->setPageUrl('#page/?id=' . $this->name . '&page=' . $i);
            yield $item;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function fetch(string $id, TextContent|PackageContent $content): bool
    {
        $content->create(md5($id), sprintf("test \n №%d content", rand(1, 100)));
        return $content->isAvailable();
    }
}
