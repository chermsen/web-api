<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Endpoint;

use GuzzleHttp\RequestOptions;

/**
 * Class Domain
 *
 * @package Myracloud\WebApi\Endpoint
 */
class Domain extends AbstractEndpoint
{
    /**
     * @var string
     */
    protected $epName = 'domains';

    /**
     * @param     $domain
     * @param int $page
     * @return mixed
     */
    public function getList($domain, $page = 1)
    {
        $uri = $this->uri . '/' . $page;
        /** @var \GuzzleHttp\Psr7\Response $res */
        $res = $this->client->get($uri);

        return $this->handleResponse($res);
    }

    /**
     * @param      $name
     * @param bool $autoUpdate
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create($name, $autoUpdate = false)
    {
        $options[RequestOptions::JSON] =
            [
                'name'       => $name,
                'autoUpdate' => $autoUpdate,
            ];

        /** @var \GuzzleHttp\Psr7\Response $res */
        $res = $this->client->request('PUT', $this->uri, $options);

        return $this->handleResponse($res);
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
        $options[RequestOptions::JSON] =
            [
                'name'     => $domain,
                'id'       => $id,
                'modified' => $modified->format('c'),
            ];
        /** @var \GuzzleHttp\Psr7\Response $res */
        $res = $this->client->request('DELETE', $this->uri, $options);

        return $this->handleResponse($res);
    }

    /**
     * @param           $id
     * @param \DateTime $modified
     * @param bool      $autoUpdate
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update($id, \DateTime $modified, $autoUpdate = false)
    {
        $options[RequestOptions::JSON] =
            [
                'id'         => $id,
                'modified'   => $modified->format('c'),
                'autoUpdate' => $autoUpdate,
            ];

        /** @var \GuzzleHttp\Psr7\Response $res */
        $res = $this->client->request('POST', $this->uri, $options);

        return $this->handleResponse($res);
    }
}