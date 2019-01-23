<?php
declare(strict_types=1);

namespace Myracloud\WebApi;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Myracloud\WebApi\Endpoint\CacheClear;
use Myracloud\WebApi\Endpoint\CacheSetting;
use Myracloud\WebApi\Endpoint\Certificate;
use Myracloud\WebApi\Endpoint\DnsRecord;
use Myracloud\WebApi\Endpoint\Domain;
use Myracloud\WebApi\Endpoint\IpFilter;
use Myracloud\WebApi\Endpoint\Maintenance;
use Myracloud\WebApi\Endpoint\Redirect;
use Myracloud\WebApi\Endpoint\Statistic;
use Myracloud\WebApi\Endpoint\SubdomainSetting;
use Myracloud\WebApi\Middleware\Signature;
use Psr\Http\Message\RequestInterface;

/**
 * Class WebApi
 *
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
     *
     * @param        $apiKey
     * @param        $secret
     * @param null   $site
     * @param string $lang
     * @param array  $connectionConfig
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

        $client       = new Client(
            array_merge(
                [
                    'base_uri' => 'https://' . $this->site . '/' . $this->lang . '/rapi',
                    'handler'  => $stack,
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
        return $this->getInstance(Domain::class);

    }

    /**
     * @param $name
     * @return mixed
     */
    private function getInstance($className)
    {
        if (!array_key_exists($className, $this->endpointCache)) {
            $this->endpointCache[$className] = new $className($this->client);
        }

        return $this->endpointCache[$className];
    }

    /**
     * @return Redirect
     * @throws \Exception
     */
    public function getRedirectEndpoint()
    {
        return $this->getInstance(Redirect::class);
    }

    /**
     * @return Redirect
     * @throws \Exception
     */
    public function getCacheSettingsEndpoint()
    {
        return $this->getInstance(CacheSetting::class);
    }

    /**
     * @return Redirect
     * @throws \Exception
     */
    public function getCertificateEndpoint()
    {
        return $this->getInstance(Certificate::class);
    }

    /**
     * @return Redirect
     * @throws \Exception
     */
    public function getSubdomainSettingsEndpoint()
    {
        return $this->getInstance(SubdomainSetting::class);
    }

    /**
     * @return Redirect
     * @throws \Exception
     */
    public function getDnsRecordEndpoint()
    {
        return $this->getInstance(DnsRecord::class);
    }

    /**
     * @return Redirect
     * @throws \Exception
     */
    public function getStatisticEndpoint()
    {
        return $this->getInstance(Statistic::class);
    }

    /**
     * @return Redirect
     * @throws \Exception
     */
    public function getMaintenanceEndpoint()
    {
        return $this->getInstance(Maintenance::class);
    }

    /**
     * @return Redirect
     * @throws \Exception
     */
    public function getIpFilterEndpoint()
    {
        return $this->getInstance(IpFilter::class);
    }

    /**
     * @return Redirect
     * @throws \Exception
     */
    public function getCacheClearEndpoint()
    {
        return $this->getInstance(CacheClear::class);
    }


}