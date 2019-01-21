<?php
declare(strict_types=1);

namespace Myracloud\Tests\Endpoint;


use Myracloud\Tests\config;
use Myracloud\WebApi\WebApi;
use PHPUnit\Framework\TestCase;

class AbstractEndpointTest extends TestCase
{
    /** @var WebApi */
    protected $Api;

    /**
     *
     */
    protected function setUp()
    {
        $config = new config();
        $config = $config->get();
        $this->Api = new \Myracloud\WebApi\WebApi(
            $config['apiKey'],
            $config['secret'],
            'beta.myracloud.com'
        );
        $this->assertThat($this->Api, $this->isInstanceOf('Myracloud\WebApi\WebApi'));

    }

    protected function assertMyraNoError($result)
    {
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals(false, $result['error']);
        $this->assertArrayHasKey('violationList', $result);
        $this->assertEquals([], $result['violationList']);
    }

}