<?php

namespace App\Tests\Helper;

final class ApiResponseField
{
    public const COMMENT_READ = ['id', 'content', 'created_at', 'updated_at'];
    public const USER_READ = ['id', 'email'];
    public const TICKER_READ = ['id', 'title', 'description', 'status', 'priority', 'user', 'created_at', 'updated_at'];
    public const PAGINATION_KEYS = ['data', 'meta', 'links'];
    public const PAGINATION_META_KEYS = ['total', 'per_page', 'current_page', 'total_pages'];
    public const PAGINATION_LINKS_KEYS = ['first', 'last', 'prev', 'next', 'current'];
}