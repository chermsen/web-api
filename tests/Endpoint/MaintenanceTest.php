<?php
declare(strict_types=1);

namespace Myracloud\Tests\Endpoint;

use Myracloud\WebApi\Endpoint\Maintenance;

/**
 * Class MaintenanceTest
 *
 * @package Myracloud\Tests\Endpoint
 */
class MaintenanceTest extends AbstractEndpointTest
{
    /** @var Maintenance */
    protected $maintenanceEndpoint;

    protected $testData = [
        'create'  => [
            'fqdn'    => self::TESTDOMAIN,
            'content' => 'Maintenance Page',
            'active'  => true,
        ],
        'update'  => [
            'fqdn'    => self::TESTDOMAIN,
            'content' => 'Maintenande Page changed',
        ],
        'default' => [
            'label'    => 'aaaaaaaaaaaaaaaa',
            'value'    => 'bbbbbbbbbbbbbbbb',
            'twitter'  => 'cccccccccccccccc',
            'facebook' => 'dddddddddddddddd',
        ],
    ];


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
    public function testUpdate()
    {
        $this->testCreate();
        $list = $this->maintenanceEndpoint->getList(self::TESTDOMAIN);

        foreach ($list['list'] as $item) {
            if ($item['content'] == $this->testData['create']['content']) {
                $result = $this->maintenanceEndpoint->update(
                    self::TESTDOMAIN,
                    $item['id'],
                    new \DateTime($item['modified']),
                    new \DateTime($item['start']),
                    new \DateTime($item['end']),
                    $this->testData['update']['content']
                );
                var_export($result);
                $this->verifyNoError($result);
                $this->verifyTargetObject($result, 'MaintenanceVO');
                $this->verifyFields($result['targetObject'][0], $this->testData['update']);
            }
        }
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCreate()
    {
        $this->testDelete();
        $endDate   = new \DateTime();
        $startDate = clone $endDate;
        $startDate->sub(new \DateInterval('P1D'));

        $result = $this->maintenanceEndpoint->create(
            self::TESTDOMAIN,
            $startDate,
            $endDate,
            $this->testData['create']['content']
        );

        $this->verifyNoError($result);
        $this->verifyTargetObject($result, 'MaintenanceVO');
        $this->verifyFields($result['targetObject'][0], $this->testData['create']);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDelete()
    {
        $list = $this->maintenanceEndpoint->getList(self::TESTDOMAIN);

        foreach ($list['list'] as $item) {
            $res = $this->maintenanceEndpoint->delete(
                self::TESTDOMAIN,
                $item['id'],
                new \DateTime($item['modified'])
            );
        }
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetList()
    {
        $this->testCreate();
        $result = $this->maintenanceEndpoint->getList(self::TESTDOMAIN);
        $this->verifyListResult($result);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCreateDefault()
    {
        $this->testDelete();
        $endDate   = new \DateTime();
        $startDate = clone $endDate;
        $startDate->sub(new \DateInterval('P1D'));

        $result = $this->maintenanceEndpoint->createDefaultPage(
            self::TESTDOMAIN,
            $startDate,
            $endDate,
            $this->testData['default']['label'],
            $this->testData['default']['value'],
            $this->testData['default']['facebook'],
            $this->testData['default']['twitter']
        );
        $this->verifyNoError($result);
        $this->verifyTargetObject($result, 'MaintenanceVO');


        $this->assertArrayHasKey('targetObject', $result);

        $this->assertEquals(1, count($result['targetObject']));

        $this->assertStringContainsString($this->testData['default']['label'], $result['targetObject'][0]['content']);
        $this->assertStringContainsString($this->testData['default']['value'], $result['targetObject'][0]['content']);
        $this->assertStringContainsString($this->testData['default']['facebook'],
            $result['targetObject'][0]['content']);
        $this->assertStringContainsString($this->testData['default']['twitter'], $result['targetObject'][0]['content']);
    }
}
