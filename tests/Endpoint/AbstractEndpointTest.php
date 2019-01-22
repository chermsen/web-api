<?php
declare(strict_types=1);

namespace Myracloud\Tests\Endpoint;


use Myracloud\Tests\config;
use Myracloud\WebApi\WebApi;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractEndpointTest
 * @package Myracloud\Tests\Endpoint
 */
abstract class AbstractEndpointTest extends TestCase
{

    /** @var WebApi */
    protected $Api;
    /**
     * @var string
     */
    protected $testDomain = 'myratest.org';

    /**
     *
     */
    protected function setUp()
    {
        $config = new config();
        $config = $config->get();
        $this->Api = new WebApi(
            $config['apiKey'],
            $config['secret'],
            'beta.myracloud.com'
        );
        $this->assertThat($this->Api, $this->isInstanceOf('Myracloud\WebApi\WebApi'));

    }

    /**
     * @param $result
     */
    protected function verifyListResult($result)
    {
        $this->verifyNoError($result);

        $this->assertArrayHasKey('page', $result);
        $this->assertEquals(1, $result['page']);


        $this->assertArrayHasKey('list', $result);
        $this->assertIsArray($result['list']);


        $this->assertArrayHasKey('count', $result);
        $this->assertEquals(count($result['list']), $result['count']);
    }

    /**
     * @param $result
     */
    protected function verifyNoError($result)
    {
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals(false, $result['error']);
    }

}