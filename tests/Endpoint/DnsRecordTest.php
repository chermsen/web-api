<?php
declare(strict_types=1);

namespace Myracloud\Tests\Endpoint;

use Myracloud\WebApi\Endpoint\DnsRecord;

/**
 * Class DnsRecordTest
 *
 * @package Myracloud\WebApi\Endpoint
 */
class DnsRecordTest extends AbstractEndpointTest
{
    /** @var DnsRecord */
    protected $dnsRecordEndpoint;

    protected $testData = [
        'create' => [
            'value'      => '123.123.123.123',
            'priority'   => 0,
            'ttl'        => 333,
            'recordType' => DnsRecord::DNS_TYPE_A,
            'active'     => true,
            'enabled'    => true,
            'paused'     => false,
            'caaFlags'   => 0,
        ],
        'update' => [
            'value'      => '12.23.34.45',
            'priority'   => 0,
            'ttl'        => 333,
            'recordType' => DnsRecord::DNS_TYPE_A,
            'active'     => false,
            'enabled'    => true,
            'paused'     => false,
            'caaFlags'   => 0,
        ],
        'list1'  => [
            'value'      => '22.222.222.222',
            'name'       => 'someOtherName',
            'priority'   => 0,
            'ttl'        => 112233,
            'recordType' => DnsRecord::DNS_TYPE_A,
            'active'     => false,
            'enabled'    => true,
            'paused'     => false,
            'caaFlags'   => 0,
        ],
        'list2'  => [
            'value'      => 'test test test',
            'name'       => 'testname',
            'priority'   => 0,
            'ttl'        => 9999,
            'recordType' => DnsRecord::DNS_TYPE_TXT,
            'active'     => false,
            'enabled'    => true,
            'paused'     => false,
            'caaFlags'   => 0,
        ],
    ];

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

        $this->testData['create']['name']             = $this->subDomain . '.' . self::TESTDOMAIN;
        $this->testData['create']['alternativeCname'] = $this->subDomain . '-' . str_replace('.', '-', self::TESTDOMAIN) . '.ax4z.com.';

        $this->testData['update']['name']             = $this->subDomain2 . '.' . self::TESTDOMAIN;
        $this->testData['update']['alternativeCname'] = $this->subDomain2 . '-' . str_replace('.', '-', self::TESTDOMAIN) . '.ax4z.com.';
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testUpdate()
    {
        $this->testCreate();
        $list = $this->dnsRecordEndpoint->getList(self::TESTDOMAIN);
        foreach ($list['list'] as $item) {
            if ($item['name'] == $this->testData['create']['name']) {
                $result = $this->dnsRecordEndpoint->update(
                    self::TESTDOMAIN,
                    $item['id'],
                    new \DateTime($item['modified']),
                    $this->subDomain2,
                    $this->testData['update']['value'],
                    $this->testData['update']['ttl'],
                    $this->testData['update']['recordType'],
                    $this->testData['update']['active']
                );
                $this->verifyNoError($result);
                $this->verifyTargetObject($result, 'DnsRecordVO');
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
        $result = $this->dnsRecordEndpoint->create(
            self::TESTDOMAIN,
            $this->subDomain,
            $this->testData['create']['value'],
            $this->testData['create']['ttl'],
            $this->testData['create']['recordType']
        );

        $this->verifyNoError($result);
        $this->verifyTargetObject($result, 'DnsRecordVO');
        $this->verifyFields($result['targetObject'][0], $this->testData['create']);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDelete()
    {
        $list = $this->dnsRecordEndpoint->getList(self::TESTDOMAIN);
        foreach ($list['list'] as $item) {
            if (
                $item['name'] == $this->testData['create']['name']
                || $item['name'] == $this->testData['update']['name']
            ) {
                $result = $this->dnsRecordEndpoint->delete(
                    self::TESTDOMAIN,
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
        $result = $this->dnsRecordEndpoint->getList(self::TESTDOMAIN);
        $this->verifyListResult($result);
    }

    /**
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetListFiltered()
    {
        $this->dnsRecordEndpoint->create(
            self::TESTDOMAIN,
            $this->subDomain,
            $this->testData['create']['value'],
            $this->testData['create']['ttl'],
            $this->testData['create']['recordType'],
            $this->testData['create']['active']
        );
        $this->dnsRecordEndpoint->create(
            self::TESTDOMAIN,
            $this->testData['list1']['name'],
            $this->testData['list1']['value'],
            $this->testData['list1']['ttl'],
            $this->testData['list1']['recordType'],
            $this->testData['list1']['active']
        );
        $this->dnsRecordEndpoint->create(
            self::TESTDOMAIN,
            $this->testData['list2']['name'],
            $this->testData['list2']['value'],
            $this->testData['list2']['ttl'],
            $this->testData['list2']['recordType'],
            $this->testData['list2']['active']

        );
        /**
         * List only A Records
         */
        $result = $this->dnsRecordEndpoint->getList(self::TESTDOMAIN, 1, null, DnsRecord::DNS_TYPE_A);
        $this->verifyNoError($result);

        $this->assertEquals(2, count($result['list']));
        foreach ($result['list'] as $item) {
            $this->assertEquals(DnsRecord::DNS_TYPE_A, $item['recordType']);
        }

        /**
         * List only active
         */
        $result = $this->dnsRecordEndpoint->getList(self::TESTDOMAIN, 1, null, null, true);

        $this->verifyNoError($result);

        $this->assertEquals(1, count($result['list']));
        foreach ($result['list'] as $item) {
            $this->assertEquals(true, $item['active']);
        }

        /**
         * List only with substring in name
         */
        $result = $this->dnsRecordEndpoint->getList(self::TESTDOMAIN, 1, 'sub');
        $this->verifyNoError($result);

        $this->assertEquals(1, count($result['list']));
        foreach ($result['list'] as $item) {
            $this->assertContains('sub', $item['name']);
        }
    }
}
