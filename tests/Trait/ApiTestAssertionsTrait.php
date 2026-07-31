<?php

namespace App\Tests\Trait;

use App\Tests\Helper\ApiResponseField;
use PHPUnit\Framework\Assert;

trait ApiTestAssertionsTrait
{
    /**
     * @param list<string> $expectedKeys
     * @param array<string, mixed> $actual
     */
    public function assertResponseApiField(array $expectedKeys, array $actual): void
    {
        Assert::assertEqualsCanonicalizing($expectedKeys, array_keys($actual));
    }

    /**
     * @param array<string, mixed> $data
     */
    public function assertPaginationStructure(array $data): void
    {
        $this->assertResponseApiField(ApiResponseField::PAGINATION_KEYS, $data);
        $this->assertResponseApiField(ApiResponseField::PAGINATION_META_KEYS, $data['meta']);
        $this->assertResponseApiField(ApiResponseField::PAGINATION_LINKS_KEYS, $data['links']);
    }
}
