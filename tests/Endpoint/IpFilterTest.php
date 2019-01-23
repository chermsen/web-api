<?php
declare(strict_types=1);

namespace Myracloud\Tests\Endpoint;

use Myracloud\WebApi\Endpoint\IpFilter;

class IpFilterTest extends AbstractEndpointTest
{
    /** @var IpFilter */
    protected $ipFilterEndpoint;

    protected $testData = [
        'create' => [
            'type' => IpFilter::IPFILTER_TYPE_BLACKLIST,
            'value' => '1.2.3.4/32'
        ],
        'update' => [
            'type' => IpFilter::IPFILTER_TYPE_WHITELIST,
            'value' => '5.6.7.8/32'
        ]
    ];

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->ipFilterEndpoint = $this->Api->getIpFilterEndpoint();
        $this->assertThat($this->ipFilterEndpoint, $this->isInstanceOf('Myracloud\WebApi\Endpoint\IpFilter'));
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetList()
    {
        $result = $this->ipFilterEndpoint->getList(self::TESTDOMAIN);
        $this->verifyListResult($result);
        var_export($result);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCreate()
    {

        $result = $this->ipFilterEndpoint->create(
            self::TESTDOMAIN,
            $this->testData['create']['type'],
            $this->testData['create']['value']

        );

        var_dump($result);
        $this->verifyNoError($result);
        $this->verifyTargetObject($result, 'IpFilterVO');
        $this->verifyFields($result['targetObject'][0], $this->testData['create']);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDelete()
    {
        $list = $this->ipFilterEndpoint->getList(self::TESTDOMAIN);

        foreach ($list['list'] as $item) {
            if (
                $item['value'] == $this->testData['create']['value']
                || $item['value'] == $this->testData['update']['value']
            ) {
                $result = $this->ipFilterEndpoint->delete(
                    self::TESTDOMAIN,
                    $item['id'],
                    new \DateTime($item['modified'])
                );
                $this->verifyNoError($result);
                $this->verifyTargetObject($result, 'IpFilterVO');
            }
        }
    }

    public function testUpdate()
    {
        $list = $this->ipFilterEndpoint->getList(self::TESTDOMAIN);

        foreach ($list['list'] as $item) {
            if ($item['value'] == $this->testData['create']['value']) {

                $result = $this->ipFilterEndpoint->update(
                    self::TESTDOMAIN,
                    $item['id'],
                    new \DateTime($item['modified']),
                    $this->testData['update']['type'],
                    $this->testData['update']['value']
                );
                var_dump($result);
                $this->verifyNoError($result);
                $this->verifyTargetObject($result, 'IpFilterVO');
                $this->verifyFields($result['targetObject'][0], $this->testData['update']);

            }
        }

    }


}
