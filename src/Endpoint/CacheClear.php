<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Endpoint;

use GuzzleHttp\RequestOptions;

/**
 * Class CacheClear
 *
 * @package Myracloud\WebApi\Endpoint
 */
class CacheClear extends AbstractEndpoint
{
    /**
     * @var string
     */
    protected $epName = 'cacheClear';

    /**
     * @param string $domain
     * @param string $fqdn
     * @param string $resource
     * @param bool   $recursive
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function clear($domain, $fqdn, $resource, $recursive = false)
    {
        $uri = $this->uri . '/' . $domain;

        $options[RequestOptions::JSON] =
            [
                "fqdn"      => $fqdn,
                "resource"  => $resource,
                "recursive" => $recursive,
            ];


        /** @var \GuzzleHttp\Psr7\Response $res */
        $res = $this->client->request('PUT', $uri, $options);

        return $this->handleResponse($res);
    }
}