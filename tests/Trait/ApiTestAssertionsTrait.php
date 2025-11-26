<?php

namespace App\Tests\Trait;

use PHPUnit\Framework\Assert;

trait ApiTestAssertionsTrait
{
    public function assertSerializedKeys(array $expectedKeys, array $actual): void
    {
        Assert::assertEqualsCanonicalizing($expectedKeys, array_keys($actual));
    }

    public function assertValue(array $actual, string $expectedContent, string $field): void
    {
        Assert::assertSame($expectedContent, $actual[$field]);
    }
}