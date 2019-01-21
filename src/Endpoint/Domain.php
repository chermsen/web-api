<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Endpoint;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;

/**
 * Class Domain
 * @package Myracloud\WebApi\Endpoint
 */
class Domain
{
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
        $this->uri = (string)$basUri->withPath($basUri->getPath() . '/domains');
    }

    /**
     * @return mixed
     */
    public function getList()
    {
        /** @var \GuzzleHttp\Psr7\Request $res */
        $res = $this->client->get($this->uri);
        return json_decode($res->getBody()->getContents(), true);
    }

    /**
     * @param $name
     * @param bool $autoUpdate
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create($name, bool $autoUpdate = false)
    {
        $options[RequestOptions::JSON] =
            [
                'name' => $name,
                'autoUpdate' => $autoUpdate
            ];

        /** @var \GuzzleHttp\Psr7\Request $res */
        $res = $this->client->request('PUT', $this->uri, $options);
        return json_decode($res->getBody()->getContents(), true);
    }

    /**
     * @param $id
     * @param \DateTime $modified
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete($id, \DateTime $modified)
    {
        $options[RequestOptions::JSON] =
            [
                'id' => $id,
                'modified' => $modified->format('c')
            ];

        /** @var \GuzzleHttp\Psr7\Request $res */
        $res = $this->client->request('DELETE', $this->uri, $options);
        return json_decode($res->getBody()->getContents(), true);
    }

    /**
     * @param $id
     * @param \DateTime $modified
     * @param bool $autoUpdate
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update($id, \DateTime $modified, $autoUpdate = false)
    {
        $options[RequestOptions::JSON] =
            [
                'id' => $id,
                'modified' => $modified->format('c'),
                'autoUpdate' => $autoUpdate,
            ];

        /** @var \GuzzleHttp\Psr7\Request $res */
        $res = $this->client->request('POST', $this->uri, $options);
        return json_decode($res->getBody()->getContents(), true);
    }
}