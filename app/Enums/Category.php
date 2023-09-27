<?php declare(strict_types=1);

namespace App\Enums;

use App\Interfaces\FilterEnum;

enum Category: string implements FilterEnum
{
    case AUDIO = 'Audio';

    case VIDEO = 'Video';

    case APPLICATIONS = 'Applications';

    case GAMES = 'Games';

    case TEXT = 'Text';

    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return 'Category';
    }

    /**
     * @inheritdoc
     */
    public static function getId(): string
    {
        return 'category';
    }
}
