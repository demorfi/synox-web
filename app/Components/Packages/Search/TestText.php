<?php declare(strict_types=1);

namespace App\Components\Packages\Search;

use App\Package\Search\Abstracts\Package;
use App\Package\Search\Enums\{Subtype, Category};
use App\Package\Search\{Query, Filter};
use App\Package\Search\Item\Text as TextItem;
use App\Package\Search\Content\Text as TextContent;

class TestText extends Package
{
    /**
     * @var Subtype
     */
    private Subtype $subtype = Subtype::TEXT;

    /**
     * @var string
     */
    private string $name = 'Test Text';

    /**
     * @var string
     */
    private string $description = 'Test search text';

    /**
     * @var string
     */
    private string $version = '1.0';

    /**
     * @inheritdoc
     */
    public function getSubtype(): Subtype
    {
        return $this->subtype;
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
    public function getDescription(): string
    {
        return $this->description;
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
    public function onlyAllowed(): Filter
    {
        return new Filter([Category::getFilterId() => [Category::TEXT]]);
    }

    /**
     * @inheritdoc
     */
    public function search(Query $query): iterable
    {
        $maxResults = rand(6, 24);
        for ($i = 1; $i <= $maxResults; $i++) {
            usleep(250000);
            $item = new TextItem($this);
            $item->setCategory(Category::TEXT);
            $item->setTitle($i . ' Test ' . $query->value);
            $item->addProperty('Info-1', 'Text 1 property ' . $i);
            $item->addProperty('Info-2', 'Text 2 property ' . $i);
            $item->setDescription('Test short content ' . $i);
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
    public function fetch(Query $query): TextContent
    {
        return $this->subtype->makeContent()
            ->create(md5($query->value), sprintf("test \n №%d content", rand(1, 100)));
    }
}