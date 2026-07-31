<?php

namespace App\Constant;

final class Roles
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public const ROLE_USER = 'ROLE_USER';

    public const ALL = [
        self::ROLE_ADMIN,
        self::ROLE_USER,
    ];
}
