<?php declare(strict_types=1);

namespace App\Abstracts;

use Stringable;
use JsonSerializable;

abstract class PackageItem implements Stringable, JsonSerializable
{
    /**
     * Package name.
     *
     * @var string
     */
    protected readonly string $package;

    /**
     * Package id.
     *
     * @var string
     */
    protected readonly string $id;

    /**
     * @var string
     */
    protected string $title = 'Unknown title';

    /**
     * @var string
     */
    protected string $fetchUrl;

    /**
     * @var string
     */
    protected string $pageUrl;

    /**
     * @param Package $package
     */
    public function __construct(Package $package)
    {
        $className     = get_class($package);
        $this->package = $package->getName();
        $this->id      = substr($className, strrpos($className, '\\') + 1);
    }

    /**
     * Get package name.
     *
     * @return string
     */
    public function getPackage(): string
    {
        return $this->package;
    }

    /**
     * Get package id.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title ?: $this->title;
    }

    /**
     * @return string
     */
    public function getFetchUrl(): string
    {
        return $this->fetchUrl;
    }

    /**
     * @param string $url
     * @return void
     */
    public function setFetchUrl(string $url): void
    {
        $this->fetchUrl = $url;
    }

    /**
     * Get url page.
     *
     * @return string
     */
    public function getPageUrl(): string
    {
        return ($this->pageUrl);
    }

    /**
     * @param string $url
     * @return void
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
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
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
