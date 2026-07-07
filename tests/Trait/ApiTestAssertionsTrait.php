<?php

namespace App\Tests\Trait;

use App\Tests\Helper\ApiResponseField;
use PHPUnit\Framework\Assert;

trait ApiTestAssertionsTrait
{
    public function assertResponseApiField(array $expectedKeys, array $actual): void
    {
        Assert::assertEqualsCanonicalizing($expectedKeys, array_keys($actual));
    }

    public function assertValue(array $actual, string $expectedContent, string $field): void
    {
        Assert::assertSame($expectedContent, $actual[$field]);
    }

    public function assertPaginationStructure(array $data): void
    {
        $this->assertResponseApiField(ApiResponseField::PAGINATION_KEYS, $data);
        $this->assertResponseApiField(ApiResponseField::PAGINATION_META_KEYS, $data['meta']);
        $this->assertResponseApiField(ApiResponseField::PAGINATION_LINKS_KEYS, $data['links']);
    }
}
