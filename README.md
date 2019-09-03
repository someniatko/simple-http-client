# simple-http-client

Writing implementation-agnostic code is a good practice, especially
for pluggable libraries - they should not require using some concrete 
HTTP client for example. That's why PSR-18 - a standard HTTP client
interface was born. 

However, directly using PSR-18 HTTP Client interface (and, subsequently, 
PSR-7 requests and PSR-17 request factories) in your projects may be 
tedious, especially if you don't need that much of flexibility it provides.

This small library solves the dilemma: it contains one class,
SimpleHttpClient, which does not implement PSR-18 on its own. However,
it takes any PSR-18 client and PSR-17 factory, and returns PSR-7 response.
This way you don't need to mess up with creating PSR-7 request manually,
however you still have all the freedom of choosing any of the PSR-18 HTTP
clients.

## Usage

```
composer require someniatko/simple-http-client
```


```php
<?php

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Someniatko\SimpleHttpClient\SimpleHttpClientInterface;
use Someniatko\SimpleHttpClient\SimpleHttpClient;

function doSomething(SimpleHttpClientInterface $httpClient): void 
{
    $httpClient->sendRequest(
        'POST', // HTTP method
        'http://example.com/api/user', // URI
        [ 'Authorization' => 'basic dXNlcjpwYXNzd29yZA==' ], // headers
        '{"name":"foo","permissions":"rwx"}' // body
    );
}

/** @var ClientInterface $psr18Client */
/** @var RequestFactoryInterface $psr17RequestFactory */
/** @var StreamFactoryInterface $psr17StreamFactory */

$client = new SimpleHttpClient(
    $psr18Client,
    $psr17RequestFactory,
    $psr17StreamFactory
);

doSomething($client);
```


## Testing

Run 
```
./vendor/bin/phpunit
```
in the project root.

