<?php declare(strict_types=1);

namespace App\Components;

use Digua\Helper as HelperBase;
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
}