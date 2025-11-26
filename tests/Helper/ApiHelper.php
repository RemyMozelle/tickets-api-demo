<?php

namespace App\Tests\Helper;

use Symfony\Component\HttpKernel\HttpKernelBrowser;

final class ApiHelper 
{
    public static function getResponseDecoded(HttpKernelBrowser $client)
    {
        $response = $client->getResponse();

        return json_decode($response->getContent(), true);
    }
}