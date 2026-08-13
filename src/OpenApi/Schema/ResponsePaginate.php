<?php

namespace App\OpenApi\Schema;

final class ResponsePaginate
{
    /**
     * @var array<string, mixed>
     */
    public array $data;

    public PaginationLinks $links;

    public PaginationMeta $meta;
}
