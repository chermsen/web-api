<?php
declare(strict_types=1);

namespace Myracloud\Tests\Endpoint;


use Myracloud\Tests\config;
use Myracloud\WebApi\WebApi;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractEndpointTest
 *
 * @package Myracloud\Tests\Endpoint
 */
abstract class AbstractEndpointTest extends TestCase
{
    const TESTDOMAIN = 'myratest.org';

    /** @var WebApi */
    protected $Api;

    /**
     *
     */
    protected function setUp()
    {
        $config    = new config();
        $config    = $config->get();
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
        $this->assertEquals(false, $result['error'], 'Result contained Error Flag.');
    }

    /**
     * @param $result
     * @param $data
     */
    protected function verifyFields($result, $data)
    {
        foreach ($data as $key => $value) {
            $this->assertArrayHasKey($key, $result, 'Expected Key ' . $key . ' was not found.');
            $this->assertEquals($value, $result[$key], $key . ' was ' . $result[$key] . ' and not expected ' . $value);
        }
    }

    /**
     * @param $result
     * @param $type
     */
    protected function verifyTargetObject($result, $type): void
    {
        $this->assertArrayHasKey('targetObject', $result);
        $this->assertGreaterThan(0, count($result['targetObject']));
        $this->assertIsArray($result['targetObject'][0]);

        $this->assertArrayHasKey('objectType', $result['targetObject'][0]);
        $this->assertEquals($type, $result['targetObject'][0]['objectType']);
    }

}