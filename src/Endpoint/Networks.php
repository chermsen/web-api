<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Endpoint;

/**
 * Class Networks
 *
 * @package Myracloud\WebApi\Endpoint
 */
class Networks extends AbstractEndpoint
{
    /**
     * @var string
     */
    protected $epName = 'networks';

    /**
     * @param       $domain
     * @param int   $page
     * @param array $params
     * @return mixed
     */
    public function getList($domain = null, $page = 1, array $params = [])
    {
        $uri = $this->uri;
        /** @var \GuzzleHttp\Psr7\Response $res */
        $res = $this->client->get($uri, ['query' => $params]);

        return $this->handleResponse($res);
    }
}