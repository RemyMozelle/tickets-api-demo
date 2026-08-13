<?php

namespace App\OpenApi\Schema;

final class PaginationMeta
{
    public int $total;

    public int $per_page;

    public int $current_page;

    public int $total_pages;
}
