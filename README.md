Myra PHP Web API Client
======

What is this?
-------------

This Library implements a minimal API layer on top of guzzlehttp/guzzle to access the Myracloud Web API.

You can either use the WebApi Class to access a number of predefined endpoints or use the Signature Middleware with 
your own GuzzleHttp/Guzzle instance, to transparently handle authentication and signing of requests.

When using the endpoints please remember that these are very thin abstractions so they will return plain arrays with 
result data. Errors and Exceptions from either Guzzle or the API will have to be handled in you code.

For more flexibility, you can use a GuzzleHttp/Guzzle instance to access the API endpoint directly. 
In this case you can simply attach the signature middleware to handle Authentication headers as seen in WebApi::_construct()

    $signature = new Signature($secret, $apiKey);
    $stack->push(
     Middleware::mapRequest(
         function (RequestInterface $request) use ($signature) {
             return $signature->signRequest($request);
         }
     )
    );

Installation
------------
via Composer:
    
    composer require myra-security-gmbh/web-api
