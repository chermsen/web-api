<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Endpoint;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Response as ResponseAlias;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;

/**
 * Class AbstractEndpoint
 *
 * @package Myracloud\WebApi\Endpoint
 */
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
    const MATCHING_TYPE_EXACT  = 'exact';

    const DNS_TYPE_A     = 'A';
    const DNS_TYPE_AAAA  = 'AAAA';
    const DNS_TYPE_MX    = 'MX';
    const DNS_TYPE_CNAME = 'CNAME';
    const DNS_TYPE_TXT   = 'TXT';
    const DNS_TYPE_NS    = 'NS';
    const DNS_TYPE_SRV   = 'SRV';
    const DNS_TYPE_CAA   = 'CAA';

    const IPFILTER_TYPE_WHITELIST = 'WHITELIST';
    const IPFILTER_TYPE_BLACKLIST = 'BLACKLIST';

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
     *
     * @param Client $client
     * @throws \Exception
     */
    public function __construct(Client $client)
    {
        if ($this->epName == null) {
            throw new \Exception('Must define endpoint name $this->epName');
        }
        /** @var Uri $basUri */
        $basUri       = $client->getConfig('base_uri');
        $this->client = $client;
        $this->uri    = (string)$basUri->withPath($basUri->getPath() . '/' . $this->epName);
    }

    /**
     * @param $domain
     * @param $id
     * @param $modified
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete($domain, $id, \DateTime $modified)
    {
        $uri = $this->uri . '/' . $domain;

        $options[RequestOptions::JSON] =
            [
                'id'       => $id,
                'modified' => $modified->format('c'),
            ];

        /** @var \GuzzleHttp\Psr7\Response $res */
        $res = $this->client->request('DELETE', $uri, $options);

        return $this->handleResponse($res);
    }

    /**
     * @param ResponseAlias $response
     * @return mixed
     */
    protected function handleResponse(ResponseAlias $response)
    {
        if ($response->getStatusCode() != 200) {
            throw new TransferException(
                'Invalid Response. ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase()
            );
        }

        return \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param     $domain
     * @param int $page
     * @return mixed
     */
    public function getList($domain, $page = 1)
    {
        $uri = $this->uri . '/' . $domain . '/' . $page;
        /** @var \GuzzleHttp\Psr7\Response $res */
        $res = $this->client->get($uri);

        return $this->handleResponse($res);
    }

    /**
     * @param $value
     * @throws \Exception
     */
    protected function validateMatchingType($value)
    {
        if (!in_array($value, [
            self::MATCHING_TYPE_EXACT,
            self::MATCHING_TYPE_PREFIX,
            self::MATCHING_TYPE_SUFFIX,
        ])) {
            throw new \Exception('Unknown Matching Type.');
        }

    }

    /**
     * @param $value
     * @throws \Exception
     */
    protected function validateRedirectType($value)
    {
        if (!in_array($value, [
            self::REDIRECT_TYPE_PERMANENT,
            self::REDIRECT_TYPE_REDIRECT,
        ])) {
            throw new \Exception('Unknown Redirect Type.');
        }
    }

    /**
     * @param $value
     * @throws \Exception
     */
    protected function validateDnsType($value)
    {
        if (!in_array($value, [
            self::DNS_TYPE_A,
            self::DNS_TYPE_AAAA,
            self::DNS_TYPE_MX,
            self::DNS_TYPE_CNAME,
            self::DNS_TYPE_TXT,
            self::DNS_TYPE_NS,
            self::DNS_TYPE_SRV,
            self::DNS_TYPE_CAA,
        ])) {
            throw new \Exception('Unknown Record Type.');
        }
    }

    /**
     * @param $value
     * @throws \Exception
     */
    protected function validateIpfilterType($value)
    {
        if (!in_array($value, [
            self::IPFILTER_TYPE_BLACKLIST,
            self::IPFILTER_TYPE_WHITELIST,
        ])) {
            throw new \Exception('Unknown IpFilter Type.');
        }
    }
}