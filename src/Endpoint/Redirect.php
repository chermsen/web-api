<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Endpoint;

use GuzzleHttp\RequestOptions;

/**
 * Class Redirect
 *
 * @package Myracloud\WebApi\Endpoint
 */
class Redirect extends AbstractEndpoint
{
    /**
     * @var string
     */
    protected $epName = 'redirects';

    /**
     * @param     $domain
     * @param int $page
     * @return mixed
     */
    public function getList($domain, $page = 1)
    {
        $uri = $this->uri . '/' . $domain . '/' . $page;

        /** @var \GuzzleHttp\Psr7\Response $res */
        $res = $this->client->get($uri);

        return $this->handleResponse($res);
    }

    /**
     * @param        $domain
     * @param        $source
     * @param        $destination
     * @param string $type
     * @param string $matchingType
     * @param bool   $expertMode
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create(
        $domain,
        $source,
        $destination,
        $type = self::REDIRECT_TYPE_REDIRECT,
        $matchingType = self::MATCHING_TYPE_PREFIX,
        $expertMode = false
    ) {
        $uri = $this->uri . '/' . $domain;

        $this->validateRedirectType($type);

        $this->validateMatchingType($matchingType);


        $options[RequestOptions::JSON] =
            [
                "source"       => $source,
                "destination"  => $destination,
                "type"         => $type,
                "matchingType" => $matchingType,
                "expertMode"   => $expertMode,
            ];

        /** @var \GuzzleHttp\Psr7\Response $res */
        $res = $this->client->request('PUT', $uri, $options);

        return $this->handleResponse($res);
    }

    /**
     * @param           $domain
     * @param           $id
     * @param \DateTime $modified
     * @param           $source
     * @param           $destination
     * @param string    $type
     * @param string    $matchingType
     * @param bool      $expertMode
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update(
        $domain,
        $id,
        \DateTime $modified,
        $source,
        $destination,
        $type = self::REDIRECT_TYPE_REDIRECT,
        $matchingType = self::MATCHING_TYPE_PREFIX,
        $expertMode = false
    ) {

        $uri = $this->uri . '/' . $domain;

        $this->validateRedirectType($type);

        $this->validateMatchingType($matchingType);

        $options[RequestOptions::JSON] =
            [
                "id"           => $id,
                'modified'     => $modified->format('c'),
                "source"       => $source,
                "destination"  => $destination,
                "type"         => $type,
                "matchingType" => $matchingType,
                "expertMode"   => $expertMode,
            ];

        /** @var \GuzzleHttp\Psr7\Response $res */
        $res = $this->client->request('POST', $uri, $options);

        return $this->handleResponse($res);

    }

}