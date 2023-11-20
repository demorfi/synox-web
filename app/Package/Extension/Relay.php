<?php declare(strict_types=1);

namespace App\Package\Extension;

use App\Package\{Abstracts\Relay as RelayAbstract, Enums\Type};

final class Relay extends RelayAbstract
{
    /**
     * @inheritdoc
     */
    public function getType(): Type
    {
        return Type::EXTENSION;
    }
}