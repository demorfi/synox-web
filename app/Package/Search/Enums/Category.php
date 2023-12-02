<?php declare(strict_types=1);

namespace App\Package\Search\Enums;

use App\Package\Search\Interfaces\FilterEnum;
use Digua\Components\ArrayCollection;

enum Category: string implements FilterEnum
{
    case AUDIO = 'Audio';

    case VIDEO = 'Video';

    case APPLICATION = 'Application';

    case GAME = 'Game';

    case TEXT = 'Text';

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return strtolower($this->name);
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return ucfirst(strtolower($this->name));
    }

    /**
     * @inheritdoc
     */
    public static function tryName(string $name): ?self
    {
        $name = strtoupper($name);
        foreach (self::cases() as $case) {
            if ($case->name == $name) {
                return $case;
            }
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public static function getFilterName(): string
    {
        return 'Category';
    }

    /**
     * @inheritdoc
     */
    public static function getFilterId(): string
    {
        return 'category';
    }

    /**
     * @param mixed $value
     * @param array $array
     * @return ?self
     */
    public static function tryFromArray(mixed $value, array $array): ?self
    {
        $from = ArrayCollection::make($array)->search($value, recursive: true)->firstKey();
        return !empty($from) ? self::tryFrom($from) : null;
    }
}