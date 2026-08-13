<?php

namespace App\OpenApi\Schema;

final class ValidationErrorResponse extends ErrorResponse
{
    /**
     * @var list<Violation>
     */
    public array $violations;
}
