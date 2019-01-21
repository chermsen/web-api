<?php
declare(strict_types=1);

namespace Myracloud\Tests\Endpoint;

use Myracloud\WebApi\Endpoint\Redirect;

/**
 * Class RedirectTest
 * @package Myracloud\Tests\Endpoint
 */
class RedirectTest extends AbstractEndpointTest
{

    /**
     * @var Redirect
     */
    protected $redirectEndpoint;
    /**
     * @var string
     */
    protected $redirSource = '/test_source';
    /**
     * @var string
     */
    protected $redirDest = '/test_dest';

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->redirectEndpoint = $this->Api->getRedirectEndpoint();
        $this->assertThat($this->redirectEndpoint, $this->isInstanceOf('Myracloud\WebApi\Endpoint\Redirect'));
    }

    public function testGetList()
    {
        $result = $this->redirectEndpoint->getList($this->testDomain);
        $this->verifyListResult($result);
        var_dump($result);
    }

    public function testCreate()
    {
        $objectFields = array(
            'objectType',
            'id',
            'modified',
            'created',
            'source',
            'destination',
            'type',
            'subDomainName',
            'matchingType',
            'sort',
        );


        $result = $this->redirectEndpoint->create($this->testDomain, $this->redirSource, $this->redirDest);
        $this->verifyNoError($result);
        $this->assertArrayHasKey('targetObject', $result);

        $this->assertEquals(1, count($result['targetObject']));

        $this->assertIsArray($result['targetObject'][0]);


        foreach ($objectFields as $field) {
            $this->assertArrayHasKey($field, $result['targetObject'][0]);
        }

        $this->assertEquals('DnsRedirectVO', $result['targetObject'][0]['objectType']);


        $this->assertEquals($this->redirSource, $result['targetObject'][0]['source']);
        $this->assertEquals($this->redirDest, $result['targetObject'][0]['destination']);
        var_dump($result);
    }


    public function testUpdate()
    {
        $list = $this->redirectEndpoint->getList($this->testDomain);
        foreach ($list['list'] as $item) {
            if ($item['source'] == $this->redirSource) {
                $res = $this->redirectEndpoint->update(
                    $this->testDomain, $item['id'],
                    new \DateTime($item['modified']),
                    '/test_source_changed',
                    '/test_destination_changed'
                );
                $this->assertArrayHasKey('targetObject', $res);
                $this->assertEquals(1, count($res['targetObject']));
                $this->assertArrayHasKey('source', $res['targetObject']);
                $this->assertEquals('/test_source_changed', $res['targetObject']['source']);
                $this->assertArrayHasKey('destination', $res['targetObject']);
                $this->assertEquals('/test_destination_changed', $res['targetObject']['destination']);
            }
        }
    }
}
