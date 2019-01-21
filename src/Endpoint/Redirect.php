<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Endpoint;

use GuzzleHttp\RequestOptions;

/**
 * Class Redirect
 * @package Myracloud\WebApi\Endpoint
 */
class Redirect extends AbstractEndpoint
{
    protected $epName = 'redirects';

    public function getList($domain, int $page = 1)
    {
        $uri = $this->uri . '/' . $domain . '/' . $page;

        /** @var \GuzzleHttp\Psr7\Request $res */
        $res = $this->client->get($uri);
        return json_decode($res->getBody()->getContents(), true);
    }

    /**
     * @param $domain
     * @param $source
     * @param $destination
     * @param string $type
     * @param string $matchingType
     * @param bool $expertMode
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

        if (!in_array($type, [
            self::REDIRECT_TYPE_PERMANENT,
            self::REDIRECT_TYPE_REDIRECT,
        ])) {
            throw new \Exception('Unknown Redirect Type.');
        }

        if (!in_array($matchingType, [
            self::MATCHING_TYPE_EXACT,
            self::MATCHING_TYPE_PREFIX,
            self::MATCHING_TYPE_SUFFIX,
        ])) {
            throw new \Exception('Unknown Matching Type.');
        }
        $options[RequestOptions::JSON] =
            [
                "source" => $source,
                "destination" => $destination,
                "type" => $type,
                "matchingType" => $matchingType,
                "expertMode" => $expertMode
            ];

        /** @var \GuzzleHttp\Psr7\Request $res */
        $res = $this->client->request('PUT', $uri, $options);
        return json_decode($res->getBody()->getContents(), true);
    }

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

        if (!in_array($type, [
            self::REDIRECT_TYPE_PERMANENT,
            self::REDIRECT_TYPE_REDIRECT,
        ])) {
            throw new \Exception('Unknown Redirect Type.');
        }

        if (!in_array($matchingType, [
            self::MATCHING_TYPE_EXACT,
            self::MATCHING_TYPE_PREFIX,
            self::MATCHING_TYPE_SUFFIX,
        ])) {
            throw new \Exception('Unknown Matching Type.');
        }

        $options[RequestOptions::JSON] =
            [
                "id" => $id,
                'modified' => $modified->format('c'),
                "source" => $source,
                "destination" => $destination,
                "type" => $type,
                "matchingType" => $matchingType,
                "expertMode" => $expertMode
            ];

        /** @var \GuzzleHttp\Psr7\Request $res */
        $res = $this->client->request('POST', $uri, $options);
        return json_decode($res->getBody()->getContents(), true);

    }

    /**
     * @param $domain
     * @param $id
     * @param $modified
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete($domain, $id, $modified)
    {
        $uri = $this->uri . '/' . $domain;

        $options[RequestOptions::JSON] =
            [
                'id' => $id,
                'modified' => $modified->format('c')
            ];

        /** @var \GuzzleHttp\Psr7\Request $res */
        $res = $this->client->request('DELETE', $uri, $options);
        return json_decode($res->getBody()->getContents(), true);
    }
}