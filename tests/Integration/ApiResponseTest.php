<?php

namespace App\Tests;

use App\Service\ApiResponse;
use Nelmio\Alice\Loader\NativeLoader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ApiResponseTest extends KernelTestCase
{

    public function testCorrectResponseWithMultipleResults()
    {
        $apiResponse = static::getContainer()->get(ApiResponse::class);

        $loader = new NativeLoader();
        $objectSet = $loader->loadFile(dirname(__DIR__, 2) . '/fixtures/user.yaml');

        [$admin, $user1, $user2] = array_values($objectSet->getObjects());

        $data = json_decode($apiResponse->createApiResponse([$admin, $user1, $user2])->getContent(), true);

        $this->assertArrayHasKey('links', $data);
        $this->assertArrayHasKey('meta', $data);
        $this->assertArrayHasKey('data', $data);
    }

    public function testCorrectResponseWithOneResult()
    {
        $apiResponse = static::getContainer()->get(ApiResponse::class);

        $loader = new NativeLoader();
        $objectSet = $loader->loadFile(dirname(__DIR__, 2) . '/fixtures/user.yaml');

        [$admin] = array_values($objectSet->getObjects());

        $data = json_decode($apiResponse->createApiResponse($admin)->getContent(), true);

        $this->assertArrayNotHasKey('links', $data);
        $this->assertArrayNotHasKey('meta', $data);
        $this->assertArrayHasKey('data', $data);
    }

    public function testApiResponseWithEmptyArray()
    {
        $apiResponse = static::getContainer()->get(ApiResponse::class);

        $data = json_decode($apiResponse->createApiResponse([])->getContent(), true);

        $this->assertArrayHasKey('links', $data);
        $this->assertArrayHasKey('meta', $data);
        $this->assertEquals([], $data['data']);
    }

    public function testApiResponseWithEmptyString()
    {
        $apiResponse = static::getContainer()->get(ApiResponse::class);

        $data = json_decode($apiResponse->createApiResponse("")->getContent(), true);

        $this->assertArrayNotHasKey('links', $data);
        $this->assertArrayNotHasKey('meta', $data);
        $this->assertEquals([], $data['data']);
    }
}
