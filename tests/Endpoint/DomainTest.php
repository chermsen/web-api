<?php
declare(strict_types=1);

namespace Myracloud\Tests\Endpoint;

use Myracloud\WebApi\Endpoint\Domain;

/**
 * Class DomainTest
 * @package Myracloud\Tests\Endpoint
 */
class DomainTest extends AbstractEndpointTest
{
    /**
     * @var Domain
     */
    protected $domainEndpoint;

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
    public function testSequence()
    {
        $this->testCreate();
        $this->testUpdate();
        $this->testGetList();
        $this->testDelete();
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCreate()
    {
        $objectFields = [
            'objectType',
            'id',
            'modified',
            'created',
            'name',
            'autoUpdate',
            'maintenance',
            'paused',
            'owned',
            'dnsRecords',
            'reversed',
            'environment'
        ];

        $result = $this->domainEndpoint->create($this->testDomain);

        $this->verifyNoError($result);

        $this->assertArrayHasKey('targetObject', $result);

        $this->assertEquals(1, count($result['targetObject']));

        $this->assertIsArray($result['targetObject'][0]);


        foreach ($objectFields as $field) {
            $this->assertArrayHasKey($field, $result['targetObject'][0]);
        }

        $this->assertEquals('DomainVO', $result['targetObject'][0]['objectType']);
        $this->assertEquals($this->testDomain, $result['targetObject'][0]['name']);

        var_dump($result);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testUpdate()
    {
        $list = $this->domainEndpoint->getList();
        foreach ($list['list'] as $item) {
            if ($item['name'] == $this->testDomain) {
                $oldValue = $item['autoUpdate'];
                $result = $this->domainEndpoint->update(
                    $item['id'],
                    new \DateTime($item['modified']),
                    !$item['autoUpdate']
                );
                $this->assertNotEquals($oldValue, $result['targetObject'][0]['autoUpdate']);
            }
        }
    }

    /**
     *
     */
    public function testGetList()
    {
        $result = $this->domainEndpoint->getList();
        var_dump($result);
        $this->verifyListResult($result);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDelete()
    {
        $list = $this->domainEndpoint->getList();
        foreach ($list['list'] as $item) {
            if ($item['name'] == $this->testDomain) {
                $result = $this->domainEndpoint->delete(
                    null,
                    $item['id'],
                    new \DateTime($item['modified'])
                );
                $this->verifyNoError($result);
            }
        }
    }
}
