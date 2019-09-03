<?php
declare(strict_types=1);

namespace Someniatko\SimpleHttpClient;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class SimpleHttpClient implements SimpleHttpClientInterface
{
    /** @var ClientInterface */
    private $httpClient;

    /** @var RequestFactoryInterface */
    private $requestFactory;

    /** @var StreamFactoryInterface */
    private $streamFactory;

    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    public function sendRequest(string $method, string $uri, array $headers, string $body): ResponseInterface
    {
        return $this->httpClient->sendRequest(
            $this->addHeaders(
                $this->requestFactory
                    ->createRequest($method, $uri)
                    ->withBody($this->streamFactory->createStream($body)),
                $headers
            )
        );
    }

    private function addHeaders(RequestInterface $request, array $headers): RequestInterface
    {
        $newRequest = $request;

        foreach ($headers as $name => $value) {
            $newRequest = $newRequest->withHeader($name, $value);
        }

        return $newRequest;
    }
}
