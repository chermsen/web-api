<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Endpoint;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;

/**
 * Class Redirect
 * @package Myracloud\WebApi\Endpoint
 */
class Redirect
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
     * Domain constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        /** @var Uri $basUri */
        $basUri = $client->getConfig('base_uri');
        $this->client = $client;
        $this->uri = (string)$basUri->withPath($basUri->getPath() . '/redirects');
    }

    public function getList($domain, int $page = 1)
    {
        $uri = $this->uri . '/' . $domain . '/' . $page;

        /** @var \GuzzleHttp\Psr7\Request $res */
        $res = $this->client->get($uri);
        return json_decode($res->getBody()->getContents(), true);
    }

    /**
     * @param $domain
     * @param $source
     * @param $destination
     * @param string $type
     * @param string $matchingType
     * @param bool $expertMode
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create(
        $domain,
        $source,
        $destination,
        $type = self::REDIRECT_TYPE_REDIRECT,
        $matchingType = self::MATCHING_TYPE_PREFIX,
        $expertMode = false
    ) {
        $uri = $this->uri . '/' . $domain;

        if (!in_array($type, [
            self::REDIRECT_TYPE_PERMANENT,
            self::REDIRECT_TYPE_REDIRECT,
        ])) {
            throw new \Exception('Unknown Redirect Type.');
        }

        if (!in_array($matchingType, [
            self::MATCHING_TYPE_EXACT,
            self::MATCHING_TYPE_PREFIX,
            self::MATCHING_TYPE_SUFFIX,
        ])) {
            throw new \Exception('Unknown Matching Type.');
        }
        $options[RequestOptions::JSON] =
            [
                "source" => $source,
                "destination" => $destination,
                "type" => $type,
                "matchingType" => $matchingType,
                "expertMode" => $expertMode
            ];

        /** @var \GuzzleHttp\Psr7\Request $res */
        $res = $this->client->request('PUT', $uri, $options);
        return json_decode($res->getBody()->getContents(), true);
    }

    public function update($domain, $id, $modified)
    {

        $uri = $this->uri . '/' . $domain;


        $options[RequestOptions::JSON] =
            [
                "source" => $source,
                "destination" => $destination,
                "type" => $type,
                "matchingType" => $matchingType,
                "expertMode" => $expertMode
            ];

        /** @var \GuzzleHttp\Psr7\Request $res */
        $res = $this->client->request('POST', $uri, $options);
        return json_decode($res->getBody()->getContents(), true);

    }
}