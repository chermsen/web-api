<?php
declare(strict_types=1);

namespace Myracloud\Tests\Endpoint;

use Myracloud\WebApi\Endpoint\Maintenance;

/**
 * Class MaintenanceTest
 * @package Myracloud\Tests\Endpoint
 */
class MaintenanceTest extends AbstractEndpointTest
{
    /** @var Maintenance */
    protected $maintenanceEndpoint;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->maintenanceEndpoint = $this->Api->getMaintenanceEndpoint();
        $this->assertThat($this->maintenanceEndpoint, $this->isInstanceOf('Myracloud\WebApi\Endpoint\Maintenance'));
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetList()
    {
        $result = $this->maintenanceEndpoint->getList($this->testDomain);
        $this->verifyListResult($result);
        var_dump($result);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCreate()
    {
        $endDate = new \DateTime();
        $startDate = clone $endDate;
        $startDate->sub(new \DateInterval('P1D'));

        $testObject = array(
            'content' => 'Maintenande Page',
            'start' => $startDate,
            'end' => $endDate,
        );

        $result = $this->maintenanceEndpoint->create($this->testDomain, $startDate, $endDate, $testObject['content']);

        $this->verifyNoError($result);

        $this->assertArrayHasKey('targetObject', $result);

        $this->assertEquals(1, count($result['targetObject']));

        $this->assertIsArray($result['targetObject'][0]);

        $this->assertArrayHasKey('objectType', $result['targetObject'][0]);
        $this->assertEquals('MaintenanceVO', $result['targetObject'][0]['objectType']);

        $this->assertArrayHasKey('fqdn', $result['targetObject'][0]);
        $this->assertEquals($this->testDomain, $result['targetObject'][0]['fqdn']);

        $this->assertArrayHasKey('start', $result['targetObject'][0]);
        $this->assertArrayHasKey('end', $result['targetObject'][0]);

        $this->assertArrayHasKey('content', $result['targetObject'][0]);
        $this->assertEquals($testObject['content'], $result['targetObject'][0]['content']);
    }


    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testUpdate()
    {
        $newContent = 'Maintenande Page changed';
        $list = $this->maintenanceEndpoint->getList($this->testDomain);

        foreach ($list['list'] as $item) {
            if ($item['content'] == 'Maintenande Page') {
                $res = $this->maintenanceEndpoint->update(
                    $this->testDomain,
                    $item['id'],
                    new \DateTime($item['modified']),
                    new \DateTime($item['start']),
                    new \DateTime($item['end']),
                    $newContent
                );
                var_export($res);
                $this->assertArrayHasKey('targetObject', $res);
                $this->assertEquals(1, count($res['targetObject']));
                $this->assertArrayHasKey('content', $res['targetObject'][0]);
                $this->assertEquals($newContent, $res['targetObject'][0]['content']);
            }
        }
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDelete()
    {
        $list = $this->maintenanceEndpoint->getList($this->testDomain);

        foreach ($list['list'] as $item) {
            $res = $this->maintenanceEndpoint->delete(
                $this->testDomain,
                $item['id'],
                new \DateTime($item['modified'])
            );
        }
    }
}
