<?php
declare(strict_types=1);

namespace Someniatko\SimpleHttpClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;

interface SimpleHttpClientInterface
{
    /**
     * @param string $method
     * @param string $uri
     * @param array<string, string|string[]> $headers
     * @param string $body
     * @throws ClientExceptionInterface
     */
    public function sendRequest(
        string $method,
        string $uri,
        array $headers,
        string $body
    ): ResponseInterface;
}
