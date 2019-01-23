<?php
declare(strict_types=1);

namespace Myracloud\Tests\Endpoint;

use Myracloud\WebApi\Endpoint\CacheClear;

class CacheClearTest extends AbstractEndpointTest
{
    /** @var CacheClear */
    protected $cacheClearEndpoint;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->cacheClearEndpoint = $this->Api->getCacheClearEndpoint();
        $this->assertThat($this->cacheClearEndpoint, $this->isInstanceOf('Myracloud\WebApi\Endpoint\CacheClear'));
    }

    public function testClear()
    {
        $result = $this->cacheClearEndpoint->clear(self::TESTDOMAIN, self::TESTDOMAIN, '*', false);
        var_dump($result);
    }
}
