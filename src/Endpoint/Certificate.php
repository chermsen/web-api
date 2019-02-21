<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Endpoint;


use GuzzleHttp\RequestOptions;

/**
 * Class Certificate
 *
 * @package Myracloud\WebApi\Endpoint
 */
class Certificate extends AbstractEndpoint
{
    /**
     * @var string
     */
    protected $epName = 'certificates';


    /**
     * @param        $domain
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create(
        $domain
    ) {
        $uri = $this->uri . '/' . $domain;

        $options[RequestOptions::JSON] =
            [
                'objectType' => 'SslCertVO',
                'cert'       => 'sdfsdfsd',
                'key'        => 'ljhxcdjlkshdkjsdhf',
            ];

        /** @var \GuzzleHttp\Psr7\Response $res */
        $res = $this->client->request('PUT', $uri, $options);

        return $this->handleResponse($res);
    }

}