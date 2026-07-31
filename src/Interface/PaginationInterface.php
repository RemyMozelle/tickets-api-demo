<?php

namespace App\Interface;

interface PaginationInterface
{
    public function getPage(): int;

    public function getLimit(): int;
}
