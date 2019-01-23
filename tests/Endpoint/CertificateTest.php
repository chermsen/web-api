<?php
declare(strict_types=1);

namespace Myracloud\Tests\Endpoint;

use Myracloud\WebApi\Endpoint\Certificate;

/**
 * Class CertificateTest
 *
 * @package Myracloud\Tests\Endpoint
 */
class CertificateTest extends AbstractEndpointTest
{

    /** @var Certificate */
    protected $certificateEndpoint;


    protected $testData = [
        'create' => [
        ],
    ];

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->certificateEndpoint = $this->Api->getCertificateEndpoint();
        $this->assertThat($this->certificateEndpoint, $this->isInstanceOf('Myracloud\WebApi\Endpoint\Certificate'));
    }

    /**
     *
     */
    public function testGetList()
    {
        $list = $this->certificateEndpoint->getList(self::TESTDOMAIN);
        var_dump($list);
    }

    public function testCreate()
    {
        $list = $this->certificateEndpoint->create(self::TESTDOMAIN);
        var_dump($list);
    }
}
