<?php

namespace App\Tests\Helper;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthHelper extends WebTestCase
{
    /**
     * Create a client with a default Authorization header.
     *
     * @param string $username
     * @param string $password
     *
     * @return \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    public static function createAuthenticatedClient($username = 'admin_1@gmail.com', $password = 'admin'): KernelBrowser
    {
        $client = static::createClient();
        
        $client->jsonRequest('POST', '/api/login_check', [
            'username' => $username,
            'password' => $password,
        ]);
        
        $response = $client->getResponse();
        $status   = $response->getStatusCode();

        if ($status !== 200) {
            throw new \RuntimeException(sprintf(
                'Authentication failed: expected status 200, got %s. Response: %s',
                $status,
                $response->getContent()
            ));
        }

        $content = $response->getContent();

        if ($content === false) {
            throw new \RuntimeException('Authentication failed: empty response content.');
        }

        $data = json_decode($content, true);

        if (!isset($data['token'])) {
            throw new \RuntimeException('Authentication failed: token not found in response.');
        }

        $client->setServerParameter(
            'HTTP_Authorization',
            sprintf('Bearer %s', $data['token'])
        );

        return $client;
    }
}
