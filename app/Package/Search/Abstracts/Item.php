<?php declare(strict_types=1);

namespace App\Package\Search\Abstracts;

use App\Package\Search\Enums\Category;
use App\Package\Search\Interfaces\{Item as PackageItemInterface, Content as PackageContentInterface};
use DateTime;

abstract class Item implements PackageItemInterface
{
    /**
     * @var ?string
     */
    protected ?string $type = null;

    /**
     * @var ?string
     */
    protected ?string $typeId = null;

    /**
     * @var ?string
     */
    protected ?string $package = null;

    /**
     * @var ?string
     */
    protected ?string $id = null;

    /**
     * @var string
     */
    protected string $category = 'Unknown';

    /**
     * @var string
     */
    protected string $title = 'Unknown title';

    /**
     * @var ?string
     */
    protected ?string $date = null;

    /**
     * @var float
     */
    protected float $size = 0;

    /**
     * @var string
     */
    protected string $weight = '0b';

    /**
     * @var ?string
     */
    protected ?string $fetchId = null;

    /**
     * @var ?string
     */
    protected ?string $pageUrl = null;

    /**
     * @var array
     */
    protected array $properties = [];

    /**
     * @var ?PackageContentInterface
     */
    protected ?PackageContentInterface $content = null;

    /**
     * @param Package $package
     */
    public function __construct(Package $package)
    {
        $this->package = $package->getName();
        $this->id      = $package->getId();
        $this->type    = $package->getSubtype()->getName();
        $this->typeId  = $package->getSubtype()->getId();
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return isset($this->{$name}) ? $this->{$name} : $this->getProperty($name);
    }

    /**
     * @inheritdoc
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @inheritdoc
     */
    public function setCategory(Category $category): void
    {
        $this->category = $category->value;
    }

    /**
     * @inheritdoc
     */
    public function getProperty(string $name): string|int|null
    {
        return $this->properties[$name] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function addProperty(string $name, string|int $value): void
    {
        $this->properties[$name] = $value;
    }

    /**
     * @inheritdoc
     */
    public function delProperty(string $name): void
    {
        if (isset($this->properties[$name])) {
            unset($this->properties[$name]);
        }
    }

    /**
     * @inheritdoc
     */
    public function setContent(PackageContentInterface $content): void
    {
        $this->content = $content;
    }

    /**
     * @inheritdoc
     */
    public function getContent(): ?PackageContentInterface
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getPackage(): string
    {
        return $this->package;
    }

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function setTitle(string $title): void
    {
        $this->title = $title ?: $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getDate(): ?string
    {
        return $this->date;
    }

    /**
     * @inheritdoc
     */
    public function setDate(int|DateTime $date): void
    {
        $this->date = (is_int($date) ? (new DateTime())->setTimestamp($date) : $date)->format('Y-m-d');
    }

    /**
     * @inheritdoc
     */
    public function getSize(): float
    {
        return $this->size;
    }

    /**
     * @inheritdoc
     */
    public function getWeight(): string
    {
        return $this->weight;
    }

    /**
     * @inheritdoc
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

    /**
     * @inheritdoc
     */
    public function getFetchId(): ?string
    {
        return $this->fetchId;
    }

    /**
     * @inheritdoc
     */
    public function setFetchId(string $id): void
    {
        $this->fetchId = $id;
    }

    /**
     * @inheritdoc
     */
    public function getPageUrl(): ?string
    {
        return $this->pageUrl;
    }

    /**
     * @inheritdoc
     */
    public function setPageUrl(string $url): void
    {
        $this->pageUrl = $url;
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        return get_object_vars($this);
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $vars = get_object_vars($this);
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $vars)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return json_encode($this->jsonSerialize());
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        return $this->__serialize();
    }
}