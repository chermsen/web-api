<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Endpoint;

use GuzzleHttp\RequestOptions;

/**
 * Class Statistic
 *
 * @package Myracloud\WebApi\Endpoint
 */
class Statistic extends AbstractEndpoint
{
    /**
     * @var string
     */
    protected $epName = 'statistic';

    /**
     * @param $domain
     * @param $id
     * @param $modified
     * @return mixed
     * @throws \Exception
     */
    public function delete($domain, $id, \DateTime $modified)
    {
        throw new \Exception('Delete is not supported on ' . __CLASS__);
    }

    /**
     * @param $query
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function query($query)
    {
        $uri = $this->uri . '/query';

        $options[RequestOptions::JSON] = $query;

        /** @var \GuzzleHttp\Psr7\Response $res */
        $res = $this->client->request('POST', $uri, $options);

        return $this->handleResponse($res);
    }
}