<?php declare(strict_types=1);

namespace App\Package;

use App\Components\File;
use App\Package\Exceptions\Upload as UploadException;
use Digua\Components\FileUpload;
use Digua\Traits\DiskPath;
use Digua\Exceptions\{
    Base as BaseException,
    Path as PathException,
    Storage as StorageException
};
use Exception;

class Update
{
    use DiskPath;

    /**
     * @var array|string[]
     */
    protected static array $defaults = [
        'diskPath' => ROOT_PATH . '/storage/packages'
    ];

    /**
     * @var State
     */
    protected readonly State $state;

    /**
     * @param File $file
     * @throws Exceptions\Source
     * @throws StorageException
     */
    public function __construct(File $file)
    {
        $this->state = new State(new Source($file));
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->state->getSource()->getFile()->getBasename();
    }

    /**
     * @return State
     */
    public function getState(): State
    {
        return $this->state;
    }

    /**
     * @return bool
     */
    public function isNameExist(): bool
    {
        return class_exists($this->state->getSource()->getName(), false);
    }

    /**
     * @return ?Adapter
     */
    public function findExist(): ?Adapter
    {
        return Repository::getInstance()->getPackages()->find($this->state->getId());
    }

    /**
     * @return bool
     */
    public function move(): bool
    {
        return rename($this->state->getSource()->getFile()->getPath(), Repository::getDiskPath($this->getName()));
    }

    /**
     * @param FileUpload $file
     * @return string
     * @throws Exceptions\Source
     * @throws PathException
     * @throws UploadException
     * @throws StorageException
     * @throws BaseException
     */
    public static function upload(FileUpload $file): string
    {
        self::throwIsBrokenDiskPath();

        if ($file->getExtension() !== 'php') {
            throw new UploadException('Only direct upload of PHP files is supported!');
        }

        if ($file->size > 20480) {
            throw new UploadException('The package must not exceed 20KB in size!');
        }

        if (!$file->isValid() || ($fileUploaded = $file->moveTo(self::getDiskPath($file->getBasename()))) === false) {
            throw new UploadException('Failed to upload package!');
        }

        try {
            $upload = new static(new File($fileUploaded));
            if ($upload->isNameExist() && !$upload->findExist()?->state()->setIgnoreState(true)) {
                throw new UploadException('Cannot start package update!');
            }
            return $upload->getName();
        } catch (Exception $e) {
            unlink($fileUploaded->getPathname());
            if ($e instanceof PathException) {
                throw new UploadException(sprintf('The package (%s) is not exist!', $file->getBasename()));
            }

            throw $e;
        }
    }

    /**
     * @param string $name
     * @return Adapter
     * @throws Exceptions\Package
     * @throws Exceptions\Source
     * @throws Exceptions\State
     * @throws PathException
     * @throws StorageException
     * @throws UploadException
     */
    public static function add(string $name): Adapter
    {
        self::throwIsBrokenDiskPath();

        try {
            $upload = new static(File::make(self::getDiskPath($name)));
            if ($upload->isNameExist() || $upload->findExist() !== null) {
                throw new UploadException('Cannot update a package that has already been load!');
            }

            $state = $upload->getState();
            $state->resetValidState(); // Run revalidation
            if (($adapter = $state->getAdapter(true)) === null) {
                throw new UploadException('The package is incorrect or invalid!');
            }

            if (!$upload->move()) {
                throw new UploadException('Failed move package to repository!');
            }

            $state->setStateValue('updated', true);
            if ($state->isIgnoreState()) {
                $state->setIgnoreState(false);
            }

            $state->save();
            Repository::getInstance()->addPackage($state);
            return $adapter;
        } catch (PathException) {
            throw new UploadException(sprintf('The package (%s) is not exist!', $name));
        }
    }
}