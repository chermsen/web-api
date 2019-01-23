<?php
declare(strict_types=1);

namespace Myracloud\WebApi;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Myracloud\WebApi\Endpoint\Domain;
use Myracloud\WebApi\Endpoint\Redirect;
use Myracloud\WebApi\Middleware\Signature;
use Psr\Http\Message\RequestInterface;

/**
 * Class WebApi
 * @package Myracloud\WebApi
 */
class WebApi
{
    /**
     * @var string
     */
    protected $apiKey = '';
    /**
     * @var string
     */
    protected $secret = '';
    /**
     * @var string
     */
    protected $site = 'api.myracloud.com';
    /**
     * @var string
     */
    protected $lang = 'en';
    /**
     * @var Client
     */
    protected $client;
    /**
     * @var array
     */
    private $endpointCache = [];

    /**
     * WebApi constructor.
     * @param $apiKey
     * @param $secret
     * @param null $site
     * @param string $lang
     * @param array $connectionConfig
     */
    public function __construct($apiKey, $secret, $site = null, $lang = 'en', $connectionConfig = [])
    {
        $this->apiKey = $apiKey;
        $this->secret = $secret;

        if ($lang != null) {
            $this->lang = $lang;
        }
        if ($site != null) {
            $this->site = $site;
        }

        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());

        $signature = new Signature($secret, $apiKey);

        #$stack->push(Middleware::prepareBody());
        $stack->push(
            Middleware::mapRequest(
                function (RequestInterface $request) use ($signature) {
                    return $signature->signRequest($request);
                }
            )
        );

        $client = new Client(
            array_merge(
                [
                    'base_uri' => 'https://' . $this->site . '/' . $this->lang . '/rapi',
                    'handler' => $stack
                ],
                $connectionConfig
            )
        );
        $this->client = $client;
    }

    /**
     * @return Domain
     * @throws \Exception
     */
    public function getDomainEndpoint()
    {
        return $this->getInstance('Domain');

    }

    /**
     * @param $name
     * @return mixed
     */
    private function getInstance($name)
    {
        if (!array_key_exists($name, $this->endpointCache)) {
            $classname = 'Myracloud\WebApi\Endpoint\\' . $name;
            $this->endpointCache[$name] = new $classname($this->client);
        }
        return $this->endpointCache[$name];
    }

    /**
     * @return Redirect
     * @throws \Exception
     */
    public function getRedirectEndpoint()
    {
        return $this->getInstance('Redirect');
    }

    /**
     * @return Redirect
     * @throws \Exception
     */
    public function getCacheSettingsEndpoint()
    {
        return $this->getInstance('CacheSetting');
    }

    /**
     * @return Redirect
     * @throws \Exception
     */
    public function getDnsRecordEndpoint()
    {
        return $this->getInstance('DnsRecord');
    }

    /**
     * @return Redirect
     * @throws \Exception
     */
    public function getStatisticEndpoint()
    {
        return $this->getInstance('Statistic');
    }

    /**
     * @return Redirect
     * @throws \Exception
     */
    public function getMaintenanceEndpoint()
    {
        return $this->getInstance('Maintenance');
    }

    /**
     * @return Redirect
     * @throws \Exception
     */
    public function getIpFilterEndpoint()
    {
        return $this->getInstance('IpFilter');
    }


}