<?php
declare(strict_types=1);

namespace Myracloud\Tests\Endpoint;

use Myracloud\WebApi\Endpoint\Domain;

/**
 * Class DomainTest
 *
 * @package Myracloud\Tests\Endpoint
 */
class DomainTest extends AbstractEndpointTest
{
    /**
     * @var Domain
     */
    protected $domainEndpoint;

    protected $testData = [
        'create' => [
            'name'        => self::TESTDOMAIN,
            'maintenance' => false,
            'paused'      => false,
            'owned'       => true,
            'reversed'    => false,
            'environment' => 'live',
        ],
    ];

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->domainEndpoint = $this->Api->getDomainEndpoint();
        $this->assertThat($this->domainEndpoint, $this->isInstanceOf('Myracloud\WebApi\Endpoint\Domain'));
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testUpdate()
    {
        $this->testCreate();
        $list = $this->domainEndpoint->getList();
        foreach ($list['list'] as $item) {
            if ($item['name'] == self::TESTDOMAIN) {
                $result = $this->domainEndpoint->update(
                    $item['id'],
                    new \DateTime($item['modified']),
                    !$item['autoUpdate']
                );
                $this->verifyNoError($result);

                $this->verifyTargetObject($result, 'DomainVO');
            }
        }
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCreate()
    {
        $this->testDelete();
        $result = $this->domainEndpoint->create(self::TESTDOMAIN);

        $this->verifyNoError($result);

        $this->verifyTargetObject($result, 'DomainVO');

        $this->verifyFields($result['targetObject'][0], $this->testData['create']);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDelete()
    {
        $list = $this->domainEndpoint->getList();
        foreach ($list['list'] as $item) {
            if ($item['name'] == self::TESTDOMAIN) {
                $result = $this->domainEndpoint->delete(
                    null,
                    $item['id'],
                    new \DateTime($item['modified'])
                );
                $this->verifyNoError($result);
            }
        }
    }

    /**
     *
     */
    public function testGetList()
    {
        $this->testCreate();
        $result = $this->domainEndpoint->getList();
        $this->verifyListResult($result);
    }
}
