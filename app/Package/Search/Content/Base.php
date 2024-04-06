<?php declare(strict_types=1);

namespace App\Package\Search\Content;

use App\Package\Search\{Abstracts\File, Enums\Subtype};

class Base extends File
{
    /**
     * @var string[]
     */
    protected static array $defaults = [
        'diskPath' => ROOT_PATH . '/public/files'
    ];

    /**
     * @inheritdoc
     */
    public function getType(): Subtype
    {
        return Subtype::BASE;
    }
}