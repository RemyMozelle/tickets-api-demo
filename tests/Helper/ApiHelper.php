<?php

namespace App\Tests\Helper;

use Symfony\Component\HttpKernel\HttpKernelBrowser;

final class ApiHelper
{
    public static function getResponseDecoded(HttpKernelBrowser $client, bool $associative = true): mixed
    {
        $response = $client->getResponse();
        $content = $response->getContent();

        if ($content === false) {
            throw new \RuntimeException('Content failed: empty response content.');
        }

        return json_decode($content, $associative);
    }
}
