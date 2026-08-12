<?php

namespace App\OpenApi\Schema;

class ErrorResponse
{
    public string $type;

    public string $title;

    public int $status;

    public string $detail;
}
