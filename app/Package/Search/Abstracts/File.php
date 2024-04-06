<?php declare(strict_types=1);

namespace App\Package\Search\Abstracts;

use App\Components\Helper;
use Digua\Exceptions\Path as PathException;
use Digua\Traits\{Configurable, DiskPath};

abstract class File extends Content
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

        $diskPath = self::getDiskPath();
        if (!empty($diskPath) && !is_dir($diskPath)) {
            mkdir($diskPath, 0755, true);
        }

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
    public function getBasename(): ?string
    {
        return !empty($this->name) ? $this->name . $this->extension : null;
    }

    /**
     * @param string $name
     * @param string $content
     * @return static
     */
    public function create(string $name, string $content): static
    {
        if ($this->is($content)) {
            $name = strtolower(preg_replace(['/[^A-Za-z0-9_]/', '/_{2,}/'], '_', $name));
            if (!empty($name)) {
                $filePath = self::getDiskPath(Helper::filterFileName($name . $this->extension));
                if (file_put_contents($filePath, $content, LOCK_EX)) {
                    $this->name      = $name;
                    $this->content   = $content;
                    $this->available = true;
                }
            }
        }

        return $this;
    }

    /**
     * @param string $name
     * @return static
     */
    public function open(string $name): static
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
            $filePath      = self::getDiskPath(Helper::filterFileName($this->getBasename()));
            $this->content = file_get_contents($filePath) ?: null;
            return $this->content;
        }

        return null;
    }

    /**
     * @return ?string
     */
    public function getPath(): ?string
    {
        if ($this->available && !empty($this->name)) {
            $filePath = self::getDiskPath(Helper::filterFileName($this->getBasename()));
            return substr($filePath, strlen(DOCUMENT_ROOT));
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        return [...$data, 'baseName' => $this->getBasename(), 'path' => $this->getPath()];
    }
}