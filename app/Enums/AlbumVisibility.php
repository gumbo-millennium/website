<?php

declare(strict_types=1);

namespace App\Enums;

final class AlbumVisibility
{
    public const HIDDEN = 'hidden';

    public const MEMBERS_ONLY = 'members';

    public const USERS = 'users';

    public const WORLD = 'any';
}
