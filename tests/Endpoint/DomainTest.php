<?php
declare(strict_types=1);

namespace Myracloud\Tests\Endpoint;


use Myracloud\WebApi\Endpoint\Domain;

class DomainTest extends AbstractEndpointTest
{
    /** @var Domain */
    protected $domainEndpoint;

    public function setUp()
    {
        parent::setUp();
        $this->domainEndpoint = $this->Api->getDomainEndpoint();
        $this->assertThat($this->domainEndpoint, $this->isInstanceOf('Myracloud\WebApi\Endpoint\Domain'));
    }

    public function testUpdate()
    {
        $testDomain = 'myratest.org';
        $list = $this->domainEndpoint->getList();
        foreach ($list['list'] as $item) {
            if ($item['name'] == $testDomain) {
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

    public function testCreate()
    {
        $testDomain = 'myratest.org';
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
            'dnsRecord',
            'reversed',
            'environment'
        ];

        $result = $this->domainEndpoint->create($testDomain);

        $this->assertMyraNoError($result);

        $this->assertArrayHasKey('targetObject', $result);

        $this->assertEquals(1, count($result['targetObject']));

        $this->assertIsArray($result['targetObject'][0]);


        foreach ($objectFields as $field) {
            $this->assertArrayHasKey($field, $result['targetObject'][0]);
        }


        $this->assertEquals('DomainVO', $result['targetObject'][0]['objectType']);
        $this->assertEquals($testDomain, $result['targetObject'][0]['name']);


        var_dump($result);
    }

    public function testGetList()
    {

        $result = $this->domainEndpoint->getList();

        $this->assertMyraNoError($result);

        $this->assertArrayHasKey('page', $result);
        $this->assertEquals(1, $result['page']);


        $this->assertArrayHasKey('list', $result);
        $this->assertIsArray($result['list']);


        $this->assertArrayHasKey('count', $result);
        $this->assertEquals(count($result['list']), $result['count']);
    }

    public function testDelete()
    {
        $testDomain = 'myratest.org';
        $list = $this->domainEndpoint->getList();
        foreach ($list['list'] as $item) {
            if ($item['name'] == $testDomain) {
                $result = $this->domainEndpoint->delete(
                    $item['id'],
                    new \DateTime($item['modified'])
                );
                $this->assertMyraNoError($result);
            }
        }
    }


}
