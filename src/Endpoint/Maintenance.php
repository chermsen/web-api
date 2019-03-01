<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Endpoint;

use GuzzleHttp\RequestOptions;

/**
 * Class Maintenance
 *
 * @package Myracloud\WebApi\Endpoint
 */
class Maintenance extends AbstractEndpoint
{
    /**
     * @var string
     */
    protected $epName = 'maintenance';


    /**
     * @param           $domain
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param null      $content
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create(
        $domain,
        \DateTime $startDate,
        \DateTime $endDate,
        $content = null
    ) {
        $uri = $this->uri . '/' . $domain;

        $options[RequestOptions::JSON] =
            [
                'content' => $content,
                'start'   => $startDate->format('c'),
                'end'     => $endDate->format('c'),
            ];


        /** @var \GuzzleHttp\Psr7\Response $res */
        $res = $this->client->request('PUT', $uri, $options);

        return $this->handleResponse($res);
    }

    /**
     * @param string      $domain
     * @param \DateTime   $startDate
     * @param \DateTime   $endDate
     * @param string|null $customLabel
     * @param string|null $customUrl
     * @param string|null $facebookUrl
     * @param string|null $twitterUrl
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createDefaultPage(
        $domain,
        \DateTime $startDate,
        \DateTime $endDate,
        $customLabel = null,
        $customUrl = null,
        $facebookUrl = null,
        $twitterUrl = null
    ) {
        $uri = $this->uri . '/' . $domain;

        $pageData = [];
        if ($facebookUrl != null) {
            $pageData['facebook'] = $facebookUrl;
        }
        if ($twitterUrl != null) {
            $pageData['twitter'] = $twitterUrl;
        }
        if ($customLabel != null) {
            $pageData['custom']['label'] = $customLabel;
        }
        if ($customUrl != null) {
            $pageData['custom']['url'] = $customUrl;
        }

        $options[RequestOptions::JSON] =
            [
                'start'       => $startDate->format('c'),
                'end'         => $endDate->format('c'),
                'defaultPage' => $pageData,
            ];

        /** @var \GuzzleHttp\Psr7\Response $res */
        $res = $this->client->request('PUT', $uri, $options);

        return $this->handleResponse($res);
    }

    /**
     * @param           $domain
     * @param           $id
     * @param \DateTime $modified
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param null      $content
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update(
        $domain,
        $id,
        \DateTime $modified,
        \DateTime $startDate,
        \DateTime $endDate,
        $content = null
    ) {
        $uri = $this->uri . '/' . $domain;

        $options[RequestOptions::JSON] =
            [
                'id'       => $id,
                'modified' => $modified->format('c'),
                'content'  => $content,
                'start'    => $startDate->format('c'),
                'end'      => $endDate->format('c'),
            ];

        /** @var \GuzzleHttp\Psr7\Response $res */
        $res = $this->client->request('POST', $uri, $options);

        return $this->handleResponse($res);
    }
}