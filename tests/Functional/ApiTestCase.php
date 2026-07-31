<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ApiTestCase extends WebTestCase
{
    /**
     * @template T of object
     *
     * @param class-string<T> $service
     * @return T
     */
    protected function getService(string $service): object
    {
        /** @var T */
        return static::getContainer()->get($service);
    }
}
