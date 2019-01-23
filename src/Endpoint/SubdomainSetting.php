<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Endpoint;


use GuzzleHttp\RequestOptions;

class SubdomainSetting extends AbstractEndpoint
{
    /**
     * @var string
     */
    protected $epName = 'subdomainSetting';

    /**
     * @param     $domain
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get($domain)
    {
        $uri = $this->uri . '/' . $domain;

        /** @var \GuzzleHttp\Psr7\Response $res */
        $res = $this->client->request('GET', $uri);

        return $this->handleResponse($res);
    }

    /**
     * @param $domain
     * @param $data
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function set($domain, array $data)
    {
        $uri = $this->uri . '/' . $domain;

        $options[RequestOptions::JSON] = $data;

        /** @var \GuzzleHttp\Psr7\Response $res */
        $res = $this->client->request('POST', $uri, $options);

        return $this->handleResponse($res);
    }


}