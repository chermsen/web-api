<?php
declare(strict_types=1);

namespace Myracloud\WebApi;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Myracloud\WebApi\Authentication\Signature;
use Psr\Http\Message\RequestInterface;

/**
 * Class WebApi
 * @package Myracloud\WebApi
 */
class WebApi
{
    /**
     * @var string
     */
    protected $apiKey = '';
    /**
     * @var string
     */
    protected $secret = '';
    /**
     * @var string
     */
    protected $site = 'api.myracloud.com';
    /**
     * @var string
     */
    protected $lang = 'en';
    /**
     * @var Client|null
     */
    protected $client = null;

    /**
     * WebApi constructor.
     * @param $apiKey
     * @param $secret
     * @param null $lang
     * @param null $site
     */
    public function __construct($apiKey, $secret, $lang = null, $site = null, $connectionConfig = [])
    {
        $this->apiKey = $apiKey;
        $this->secret = $secret;

        if ($lang != null) {
            $this->lang = $lang;
        }
        if ($site != null) {
            $this->site = $site;
        }

        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());

        $stack->push(Middleware::prepareBody());
        $stack->push(
            Middleware::mapRequest(
                function (RequestInterface $request) use ($secret, $apiKey) {
                    $signature = new Signature($secret, $apiKey);
                    return $signature->signRequest($request);
                }
            )
        );


        $client = new Client(
            array_merge(
                [
                    'base_uri' => $this->site,
                    'handler' => $stack
                ],
                $connectionConfig
            )
        );
        $this->client = $client;
    }

    public function getClient()
    {
        return $this->client;
    }
}