<?php declare(strict_types=1);

namespace App\Components\Packages;

use App\Components\Storage\{Journal, Elastic};
use App\Package\Search\{Abstracts\Package, Enums\Subtype, Query, Item\Base as BaseItem, Content\Base as BaseContent};
use Digua\Components\Storage;
use Digua\Exceptions\Base as BaseException;
use Exception;

class IndexerSearch extends Package
{
    /**
     * @var Subtype
     */
    private Subtype $subtype = Subtype::BASE;

    /**
     * @var string
     */
    private string $name = 'Indexer search';

    /**
     * @var string
     */
    private string $description = 'Search by index';

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
    public function isAvailable(): bool
    {
        try {
            if (class_exists('Elasticsearch\Client')) {
                Storage::make(Elastic::class, '');
                return true;
            }
        } catch (BaseException) {
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getRequires(): array
    {
        return ['Indexer', 'Elasticsearch'];
    }

    /**
     * @return string
     */
    private function getStorageType(): string
    {
        return $this->version . '-' . 'eb-indexer';
    }

    /**
     * @inheritdoc
     */
    public function search(Query $query): iterable
    {
        try {
            /* @var Elastic $storage */
            $storage = Storage::make(Elastic::class, $this->getStorageType());
            $data    = (array)$storage->search(['match' => ['title' => $query->value]]);
            foreach ($data as $item) {
                try {
                    yield $this->buildItem($item['_source']);
                } catch (Exception $e) {
                    Journal::staticPush($this->getName() . ':' . __LINE__ . ' -> ' . $e->getMessage());
                }
            }
        } catch (BaseException) {
            return false;
        }
    }

    /**
     * @param array $info
     * @return BaseItem
     */
    protected function buildItem(array $info): BaseItem
    {
        $type  = Subtype::tryName($info['typeId']);
        $iKeys = ['package'];
        $cKeys = ['type', 'typeId', 'extension', 'path', 'baseName'];

        if (isset($info['content']['typeId'])) {
            $content = $type->makeContent();
            $content->__unserialize(array_diff_key($info['content'], array_fill_keys($cKeys, null)));
            $info['content'] = $content;
        }

        $item = $type->makeItem($this);
        $item->__unserialize(array_diff_key($info, array_fill_keys($iKeys, null)));

        $item->addProperty('Alias', $info['package']);
        return $item;
    }

    /**
     * @inheritdoc
     */
    public function fetch(Query $query): ?BaseContent
    {
        return null;
    }
}