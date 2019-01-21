<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Endpoint;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;

abstract class AbstractEndpoint
{
    /**
     * HTTP 301
     */
    const REDIRECT_TYPE_PERMANENT = 'permanent';
    /**
     * HTTP 302
     */
    const REDIRECT_TYPE_REDIRECT = 'redirect';

    const MATCHING_TYPE_PREFIX = 'prefix';
    const MATCHING_TYPE_SUFFIX = 'suffix';
    const MATCHING_TYPE_EXACT = 'exact';

    /**
     * @var Client
     */
    protected $client;
    /**
     * @var string
     */
    protected $uri;

    /**
     * @var
     */
    protected $epName = null;

    /**
     * Domain constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        if ($this->epName == null) {
            throw new \Exception('Must define endpoint name $this->epName');
        }
        /** @var Uri $basUri */
        $basUri = $client->getConfig('base_uri');
        $this->client = $client;
        $this->uri = (string)$basUri->withPath($basUri->getPath() . '/' . $this->epName);
    }

}