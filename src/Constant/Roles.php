<?php

namespace App\Constant;

final class Roles
{
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_USER = 'ROLE_USER';
    const ALL = [
        self::ROLE_ADMIN,
        self::ROLE_USER,
    ];
}
