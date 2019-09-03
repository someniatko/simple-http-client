<?php
declare(strict_types=1);

namespace Test;

use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Someniatko\SimpleHttpClient\SimpleHttpClient;

final class TestRequest implements RequestInterface
{
    /** @var string[][] */
    private $headers = [];

    /** @var StreamInterface */
    private $body;

    /** @var UriInterface */
    private $uri;

    /** @var string */
    private $method;

    public function __construct(UriInterface $uri, string $method)
    {
        $this->uri = $uri;
        $this->method = $method;
    }

    public function getProtocolVersion() { }
    public function withProtocolVersion($version) { }
    public function getHeaderLine($name) { }
    public function getRequestTarget() { }
    public function withRequestTarget($requestTarget) { }
    public function withMethod($method) { }
    public function withUri(UriInterface $uri, $preserveHost = false) { }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function hasHeader($name)
    {
        return isset($this->headers[$name]);
    }

    public function getHeader($name)
    {
        return $this->headers[$name];
    }

    public function withHeader($name, $value)
    {
        $newRequest = clone $this;
        $newRequest->headers[$name] = is_array($value) ? $value : [ $value ];

        return $newRequest;
    }

    public function withAddedHeader($name, $value)
    {
        $arrValue = is_array($value) ? $value : [ $value ];

        return $this->withHeader($name, array_merge($this->headers[$name] ?? [], $arrValue));
    }

    public function withoutHeader($name)
    {
        $newRequest = clone $this;
        unset($newRequest->headers[$name]);

        return $newRequest;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body)
    {
        $newRequest = clone $this;
        $newRequest->body = $body;

        return $newRequest;
    }

    public function getMethod()
    {
        return $this->method;
    }


    public function getUri()
    {
        return $this->uri;
    }
}

final class SimpleHttpClientTest extends TestCase
{
    public function testSendRequest(): void
    {
        $client = new SimpleHttpClient(
            $httpClient = $this->createMock(ClientInterface::class),
            $requestFactory = $this->createMock(RequestFactoryInterface::class),
            $streamFactory = $this->createMock(StreamFactoryInterface::class)
        );

        $uri = 'http://example.com/api/user';
        $uriObject = $this->createMock(UriInterface::class);

        $requestFactory
            ->method('createRequest')
            ->with('POST', $uri)
            ->willReturn(new TestRequest($uriObject, 'POST'));

        $streamFactory
            ->method('createStream')
            ->with('{"name":"foo","permissions":"rwx"}')
            ->willReturn($stream = $this->createMock(StreamInterface::class));

        $httpClient
            ->method('sendRequest')
            ->with($this->callback(function (RequestInterface $request) use ($stream, $uriObject) {
                return $request->getMethod() === 'POST'
                    && $request->getUri() === $uriObject
                    && $request->getBody() === $stream
                    && $request->getHeaders() === [
                        'Authorization' => [ 'basic dXNlcjpwYXNzd29yZA==' ],
                        'Content-Type' => [ 'application/json' ],
                    ];
            }))
            ->willReturn($expectedResponse = $this->createMock(ResponseInterface::class));

        $response = $client->sendRequest(
            'POST',
            $uri,
            [
                'Authorization' => 'basic dXNlcjpwYXNzd29yZA==',
                'Content-Type' => [ 'application/json' ]
            ],
            '{"name":"foo","permissions":"rwx"}'
        );

        $this->assertSame($expectedResponse, $response);
    }
}
