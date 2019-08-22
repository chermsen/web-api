<?php
declare(strict_types=1);

namespace Myracloud\Tests\Endpoint;

use Myracloud\WebApi\Endpoint\Networks;
use Myracloud\WebApi\Endpoint\Redirect;
use PHPUnit\Framework\TestCase;

/**
 * Class NetworksTest
 *
 * @package Myracloud\Tests\Endpoint
 */
class NetworksTest extends AbstractEndpointTest
{

    /**
     * @var Networks
     */
    protected $networksEndpoint;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->networksEndpoint = $this->Api->getNetworksEndpoint();
        $this->assertThat($this->networksEndpoint, $this->isInstanceOf('Myracloud\WebApi\Endpoint\Networks'));
    }

    /**
     *
     */
    public function testGetList()
    {
        $list = $this->networksEndpoint->getList(self::TESTDOMAIN);
        $this->verifyNoError($list);

        $this->verifyListResult($list);
        foreach ($list['list'] as $item) {
            $this->verifyFields($item, [
                'objectType' => 'IpRangeVO',
            ]);
        }
    }
}
