<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Endpoint;


use GuzzleHttp\RequestOptions;

class IpFilter extends AbstractEndpoint
{
    /**
     * @var string
     */
    protected $epName = 'ipfilter';

    /**
     * @param     $domain
     * @param int $page
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getList($domain, int $page = 1)
    {
        $uri = $this->uri . '/' . $domain . '/' . $page;

        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = $this->client->request('GET', $uri);

        return $this->handleResponse($response);
    }

    /**
     * @param $domain
     * @param $type
     * @param $value
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create(
        $domain,
        $type,
        $value
    ) {
        $uri = $this->uri . '/' . $domain;

        $this->validateIpfilterType($type);
        $options[RequestOptions::JSON] =
            [
                'type'  => $type,
                'value' => $value,
            ];


        /** @var \GuzzleHttp\Psr7\Response $res */
        $res = $this->client->request('PUT', $uri, $options);

        return $this->handleResponse($res);
    }


    public function update(
        $domain,
        $id,
        \DateTime $modified,
        $type,
        $value
    ) {
        $uri = $this->uri . '/' . $domain;

        $this->validateIpfilterType($type);
        $options[RequestOptions::JSON] =
            [
                'id'       => $id,
                'modified' => $modified->format('c'),
                'type'     => $type,
                'value'    => $value,
            ];
        /** @var \GuzzleHttp\Psr7\Response $res */
        $res = $this->client->request('POST', $uri, $options);

        return $this->handleResponse($res);
    }
}