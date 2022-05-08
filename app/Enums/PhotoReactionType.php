<?php

declare(strict_types=1);

namespace App\Enums;

enum PhotoReactionType: string
{
    case Like = 'like';
    case Dislike = 'dislike';
    case Love = 'love';
}
