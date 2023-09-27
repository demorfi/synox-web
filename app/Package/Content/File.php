<?php declare(strict_types=1);

namespace App\Package\Content;

use App\Abstracts\PackageContent;
use App\Components\Helper;
use Digua\Exceptions\Path as PathException;
use Digua\Traits\{Configurable, DiskPath};

abstract class File extends PackageContent
{
    use Configurable, DiskPath;

    /**
     * @var ?string
     */
    protected ?string $name = null;

    /**
     * @throws PathException
     */
    public function __construct()
    {
        parent::__construct();
        self::throwIsBrokenDiskPath();
    }

    /**
     * @return ?string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return ?string
     */
    public function getBaseName(): ?string
    {
        return !empty($this->name) ? $this->name . $this->extension : null;
    }

    /**
     * @param string $name
     * @param string $content
     * @return self
     */
    public function create(string $name, string $content): self
    {
        if ($this->is($content)) {
            $filePath = self::getDiskPath(Helper::filterFileName($name . $this->extension));
            if (file_put_contents($filePath, $content, LOCK_EX)) {
                $this->name      = $name;
                $this->available = true;
            }
        }

        return $this;
    }

    /**
     * @param string $name
     * @return self
     */
    public function open(string $name): self
    {
        $filePath = self::getDiskPath(Helper::filterFileName($name . $this->extension));
        if (is_readable($filePath) && filesize($filePath)) {
            $this->name      = $name;
            $this->available = true;
        }

        return $this;
    }

    /**
     * @return ?string
     */
    public function fetch(): ?string
    {
        if ($this->available && !empty($this->name)) {
            $filePath = self::getDiskPath(Helper::filterFileName($this->getBaseName()));
            return file_get_contents($filePath);
        }

        return null;
    }

    /**
     * @return ?string
     */
    public function getPath(): ?string
    {
        if ($this->available && !empty($this->name)) {
            $filePath = self::getDiskPath(Helper::filterFileName($this->getBaseName()));
            return substr($filePath, strlen(DOCUMENT_ROOT));
        }

        return null;
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        return [...$data, 'baseName' => $this->getBaseName(), 'path' => $this->getPath()];
    }
}