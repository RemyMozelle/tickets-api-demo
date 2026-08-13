<?php

namespace App\OpenApi\Schema;

final class Violation
{
    public string $propertyPath;

    public string $title;

    public string $template;

    /**
     * @var array<string, string>
     */
    public array $parameters;
}
