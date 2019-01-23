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
            'recordType' => 'A',
            'active'     => true,
            'enabled'    => true,
            'paused'     => false,
            'caaFlags'   => 0,
        ],
        'update' => [
            'value'      => '12.23.34.45',
            'priority'   => 0,
            'ttl'        => 333,
            'recordType' => 'A',
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
        $this->testData['create']['alternativeCname'] = $this->subDomain . '-' . self::TESTDOMAIN . '.ax4z.com.';

        $this->testData['update']['name']             = $this->subDomain2 . '.' . self::TESTDOMAIN;
        $this->testData['update']['alternativeCname'] = $this->subDomain2 . '-' . self::TESTDOMAIN . '.ax4z.com.';
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testSequence()
    {
        $this->testDelete();
        $this->testCreate();
        $this->testUpdate();
        $this->testGetList();
        $this->testDelete();
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCreate()
    {

        $result = $this->dnsRecordEndpoint->create(
            self::TESTDOMAIN,
            $this->subDomain,
            $this->testData['create']['value'],
            $this->testData['create']['ttl'],
            DnsRecord::DNS_TYPE_A
        );

        $this->verifyNoError($result);
        $this->verifyTargetObject($result, 'DnsRecordVO');
        $this->verifyFields($result['targetObject'][0], $this->testData['create']);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testUpdate()
    {
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
                var_export($result);
                $this->verifyNoError($result);
                $this->verifyTargetObject($result, 'DnsRecordVO');
                $this->verifyFields($result['targetObject'][0], $this->testData['update']);
            }
        }
    }

    /**
     *
     */
    public function testGetList()
    {
        $result = $this->dnsRecordEndpoint->getList(self::TESTDOMAIN);
        $this->verifyListResult($result);
        var_dump($result);
        #  var_export(array_keys($result['list'][0]));
    }

    /**
     * @throws \Exception
     */
    public function testGetListFiltered()
    {
        $result = $this->dnsRecordEndpoint->getList(self::TESTDOMAIN, 1, 'sub');
        var_dump($result);
        $result = $this->dnsRecordEndpoint->getList(self::TESTDOMAIN, 1, null, DnsRecord::DNS_TYPE_A);
        var_dump($result);
        $result = $this->dnsRecordEndpoint->getList(self::TESTDOMAIN, 1, null, null, true);
        var_dump($result);
        $result = $this->dnsRecordEndpoint->getList(self::TESTDOMAIN, 1, null, null, false, true);
        var_dump($result);
        #  var_export(array_keys($result['list'][0]));
    }
}
