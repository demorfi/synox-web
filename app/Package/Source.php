<?php declare(strict_types=1);

namespace App\Package;

use App\Components\File;
use App\Package\Enums\Type;
use App\Package\Search\{
    Interfaces\Package as SearchPackageInterface,
    Enums\Subtype as SearchSubtype
};
use App\Package\Extension\{
    Interfaces\Package as ExtensionPackageInterface,
    Enums\Subtype as ExtensionSubtype
};
use App\Package\Exceptions\Source as SourceException;
use Throwable;

class Source
{
    /**
     * @var string
     */
    private readonly string $className;

    /**
     * @var string
     */
    private readonly string $namespace;

    /**
     * @var Type
     */
    private readonly Type $type;

    /**
     * @var SearchSubtype|ExtensionSubtype
     */
    private readonly SearchSubtype|ExtensionSubtype $subtype;

    /**
     * @param File $file
     * @throws SourceException
     */
    public function __construct(protected File $file)
    {
        $source = $this->calculate();

        $this->setNamespace($source['namespace']);
        $this->setClassName($source['name']);
        $this->setType($source['type']);
        $this->setSubtype($this->type, $source['subtype']);
    }

    /**
     * @return File
     */
    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * @return array
     * @throws SourceException
     */
    private function calculate(): array
    {
        $baseName = $this->file->getBasename();
        preg_match('/^(?P<type>\w+)@(?P<subtype>\w+)\$(?P<name>\w+)(\.\w+)?$/', $baseName, $results);
        if (empty($results)) {
            throw new SourceException(sprintf('Package name (%s) is not valid!', $baseName));
        }

        $content = $this->file->getContent();
        preg_match('/^namespace\s+(?P<namespace>.+?);.*class\s+(?P<class>\w+).+;$/sm', $content, $source);
        $results['namespace'] = $source['namespace'] ?? '';
        return array_intersect_key($results, array_flip(['name', 'namespace', 'type', 'subtype']));
    }

    /**
     * @param string $className
     * @return void
     */
    private function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @param string $namespace
     * @return void
     */
    private function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->namespace . '\\' . $this->className;
    }

    /**
     * @param string $typeName
     * @return void
     * @throws SourceException
     */
    private function setType(string $typeName): void
    {
        if (($type = Type::tryName($typeName)) === null) {
            throw new SourceException(sprintf('Type (%s) in package (%s) is not valid!', $typeName, $this->className));
        }
        $this->type = $type;
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @param Type   $type
     * @param string $subtypeName
     * @return void
     * @throws SourceException
     */
    private function setSubtype(Type $type, string $subtypeName): void
    {
        if (($subtype = $type->trySubtypeName($subtypeName)) === null) {
            throw new SourceException(sprintf('Subtype (%s) in package (%s) is not valid!', $subtypeName, $this->className));
        }
        $this->subtype = $subtype;
    }

    /**
     * @return SearchSubtype|ExtensionSubtype
     */
    public function getSubtype(): SearchSubtype|ExtensionSubtype
    {
        return $this->subtype;
    }

    /**
     * @return bool
     */
    private function load(): bool
    {
        return (include_once $this->file->getPath()) == true;
    }

    /**
     * @param int $validSize
     * @return bool
     */
    public function isValidFileSize(int $validSize): bool
    {
        return $this->file->getFileSize() === $validSize;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        try {
            if ($this->file->getFileSize() < 1 || class_exists($this->getName(), false)) {
                return false;
            }

            token_get_all($this->file->getContent(), TOKEN_PARSE);
            $this->load();

            return class_exists($this->getName(), false) && is_subclass_of($this->getName(), $this->type->getInterface());
        } catch (Throwable) {
        }
        return false;
    }

    /**
     * @param ...$arguments
     * @return ExtensionPackageInterface|SearchPackageInterface
     * @throws SourceException
     */
    public function tryNewInstance(...$arguments): ExtensionPackageInterface|SearchPackageInterface
    {
        if (!$this->load()) {
            throw new SourceException(sprintf('Failed to load package (%s)', $this->file->getBasename()));
        }

        $instance = new ($this->getName())(...$arguments);
        if ($instance->getSubtype() !== $this->subtype) {
            throw new SourceException(sprintf('Wrong subtype in package (%s)', $this->file->getBasename()));
        }

        return $instance;
    }
}