<?php

namespace App\Tests\Helper;

use Symfony\Component\HttpKernel\HttpKernelBrowser;

final class ApiHelper 
{
    public static function getResponseDecoded(HttpKernelBrowser $client, bool $associative = true): mixed
    {
        $response = $client->getResponse();

        return json_decode($response->getContent(), $associative);
    }
}