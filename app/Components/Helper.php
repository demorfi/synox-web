<?php declare(strict_types=1);

namespace App\Components;

use Digua\Helper as HelperBase;
use Digua\Exceptions\{Path as PathException, Storage as StorageException};
use DOMWrap\Document;

class Helper extends HelperBase
{
    /**
     * @param mixed $markup
     * @return Document
     */
    public static function document(mixed $markup = ''): Document
    {
        return (new Document())->html($markup);
    }

    /**
     * @param string $name
     * @return Config
     * @throws PathException
     * @throws StorageException
     */
    public static function config(string $name): Config
    {
        return new Config($name);
    }
}