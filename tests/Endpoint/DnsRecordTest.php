<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Endpoint;

use Myracloud\Tests\Endpoint\AbstractEndpointTest;

class DnsRecordTest extends AbstractEndpointTest
{
    /** @var DnsRecord */
    protected $dnsRecordEndpoint;

    protected $subDomain = 'subdomain';
    protected $subDomain2 = 'otherdomain';

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->dnsRecordEndpoint = $this->Api->getDnsRecordEndpoint();
        $this->assertThat($this->dnsRecordEndpoint, $this->isInstanceOf('Myracloud\WebApi\Endpoint\DnsRecord'));
    }
    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testSequence()
    {
        $this->testCreate();
        $this->testUpdate();
        $this->testGetList();
        $this->testGetListFiltered();
        $this->testDelete();
    }
    /**
     *
     */
    public function testGetList()
    {
        $result = $this->dnsRecordEndpoint->getList($this->testDomain);
        $this->verifyListResult($result);
        var_dump($result);
        #  var_export(array_keys($result['list'][0]));
    }

    public function testGetListFiltered()
    {
        $result = $this->dnsRecordEndpoint->getList($this->testDomain, 1, 'sub');
        var_dump($result);
        $result = $this->dnsRecordEndpoint->getList($this->testDomain, 1, null, DnsRecord::DNS_TYPE_A);
        var_dump($result);
        $result = $this->dnsRecordEndpoint->getList($this->testDomain, 1, null, null, true);
        var_dump($result);
        #  var_export(array_keys($result['list'][0]));
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCreate()
    {
        $testObject = array(
            'objectType' => 'DnsRecordVO',
            'id' => null,
            'modified' => null,
            'created' => null,
            'name' => $this->subDomain . '.' . $this->testDomain,
            'value' => '123.123.123.123',
            'priority' => 0,
            'ttl' => 333,
            'recordType' => 'A',
            'active' => true,
            'enabled' => true,
            'paused' => false,
            'upstreamOptions' =>
                array(
                    'backup' => false,
                    'down' => false,
                ),
            'alternativeCname' => 'subdomain-myratest-org.ax4z.com.',
            'caaFlags' => 0,
        );

        $result = $this->dnsRecordEndpoint->create(
            $this->testDomain,
            $this->subDomain,
            $testObject['value'],
            $testObject['ttl'],
            DnsRecord::DNS_TYPE_A
        );

        $this->verifyNoError($result);

        $this->assertArrayHasKey('targetObject', $result);

        $this->assertEquals(1, count($result['targetObject']));

        $this->assertIsArray($result['targetObject'][0]);


        foreach ($testObject as $field => $value) {
            $this->assertArrayHasKey($field, $result['targetObject'][0]);
            if ($value !== null) {
                $this->assertEquals($value, $result['targetObject'][0][$field]);
            }
        }
    }


    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDelete()
    {
        $list = $this->dnsRecordEndpoint->getList($this->testDomain);
        foreach ($list['list'] as $item) {
            if (
                $item['name'] == $this->subDomain . '.' . $this->testDomain
                || $item['name'] == $this->subDomain2 . '.' . $this->testDomain
            ) {
                $result = $this->dnsRecordEndpoint->delete(
                    $this->testDomain,
                    $item['id'],
                    new \DateTime($item['modified'])
                );
                $this->verifyNoError($result);
            }
        }
    }


    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testUpdate()
    {
        $list = $this->dnsRecordEndpoint->getList($this->testDomain);
        foreach ($list['list'] as $item) {
            if ($item['name'] == $this->subDomain . '.' . $this->testDomain) {
                $res = $this->dnsRecordEndpoint->update(
                    $this->testDomain, $item['id'],
                    new \DateTime($item['modified']),
                    $this->subDomain2,
                    '222.222.222.222',
                    554,
                    DnsRecord::DNS_TYPE_A,
                    false
                );
                var_export($res);
                $this->assertArrayHasKey('targetObject', $res);
                $this->assertEquals(1, count($res['targetObject']));
                $this->assertArrayHasKey('name', $res['targetObject'][0]);
                $this->assertEquals($this->subDomain2 . '.' . $this->testDomain, $res['targetObject'][0]['name']);

                $this->assertArrayHasKey('recordType', $res['targetObject'][0]);
                $this->assertEquals('A', $res['targetObject'][0]['recordType']);

                $this->assertArrayHasKey('recordType', $res['targetObject'][0]);
                $this->assertEquals('A', $res['targetObject'][0]['recordType']);

                $this->assertArrayHasKey('ttl', $res['targetObject'][0]);
                $this->assertEquals(554, $res['targetObject'][0]['ttl']);

                $this->assertArrayHasKey('active', $res['targetObject'][0]);
                $this->assertEquals(false, $res['targetObject'][0]['active']);
            }
        }
    }


}
