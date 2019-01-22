<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Endpoint;


use GuzzleHttp\RequestOptions;

/**
 * Class CacheSetting
 * @package Myracloud\WebApi\Endpoint
 */
class CacheSetting extends AbstractEndpoint
{
    /**
     * @var string
     */
    protected $epName = 'cacheSettings';

    /**
     * @param $domain
     * @param int $page
     * @return mixed
     */
    public function getList($domain, int $page = 1)
    {
        $uri = $this->uri . '/' . $domain . '/' . $page;

        /** @var \GuzzleHttp\Psr7\Request $res */
        $res = $this->client->get($uri);
        return json_decode($res->getBody()->getContents(), true);
    }

    /**
     * @param $domain
     * @param $path
     * @param $ttl
     * @param string $type
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create(
        $domain,
        $path,
        $ttl,
        $type = self::MATCHING_TYPE_PREFIX
    ) {
        $uri = $this->uri . '/' . $domain;


        $this->validateMatchingType($type);

        $options[RequestOptions::JSON] =
            [
                "path" => $path,
                "ttl" => $ttl,
                "type" => $type
            ];

        /** @var \GuzzleHttp\Psr7\Request $res */
        $res = $this->client->request('PUT', $uri, $options);
        return json_decode($res->getBody()->getContents(), true);
    }


    /**
     * @param $domain
     * @param $id
     * @param \DateTime $modified
     * @param $path
     * @param $ttl
     * @param string $type
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update(
        $domain,
        $id,
        \DateTime $modified,
        $path,
        $ttl,
        $type = self::MATCHING_TYPE_PREFIX
    ) {

        $uri = $this->uri . '/' . $domain;


        $this->validateMatchingType($type);

        $options[RequestOptions::JSON] =
            [
                "id" => $id,
                'modified' => $modified->format('c'),
                "path" => $path,
                "ttl" => $ttl,
                "type" => $type,
            ];

        /** @var \GuzzleHttp\Psr7\Request $res */
        $res = $this->client->request('POST', $uri, $options);
        return json_decode($res->getBody()->getContents(), true);
    }


}