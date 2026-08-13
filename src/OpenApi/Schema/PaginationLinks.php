<?php

namespace App\OpenApi\Schema;

final class PaginationLinks
{
    public ?string $first;

    public ?string $last;

    public ?string $prev;

    public ?string $next;

    public string $current;
}
