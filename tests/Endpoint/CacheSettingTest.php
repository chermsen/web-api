<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Endpoint;

use Myracloud\Tests\Endpoint\AbstractEndpointTest;

/**
 * Class CacheSettingTest
 * @package Myracloud\WebApi\Endpoint
 */
class CacheSettingTest extends AbstractEndpointTest
{
    /** @var CacheSetting */
    protected $cacheSettingsEndpoint;
    /**
     * @var string
     */
    protected $testPath = '/testPath';
    /**
     * @var string
     */
    protected $testPath2 = '/test';

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->cacheSettingsEndpoint = $this->Api->getCacheSettingsEndpoint();
        $this->assertThat($this->cacheSettingsEndpoint, $this->isInstanceOf('Myracloud\WebApi\Endpoint\CacheSetting'));
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
        $objectFields = array(
            'objectType',
            'id',
            'modified',
            'created',
            'path',
            'ttl',
            'notFoundTtl',
            'type',
            'enforce',
            'sort',
        );

        $result = $this->cacheSettingsEndpoint->create($this->testDomain, $this->testPath, 300);

        var_dump($result);

        $this->verifyNoError($result);

        $this->assertArrayHasKey('targetObject', $result);

        $this->assertEquals(1, count($result['targetObject']));

        $this->assertIsArray($result['targetObject'][0]);


        foreach ($objectFields as $field) {
            $this->assertArrayHasKey($field, $result['targetObject'][0]);
        }


        $this->assertEquals('CacheSettingVO', $result['targetObject'][0]['objectType']);
        $this->assertEquals($this->testPath, $result['targetObject'][0]['path']);

    }

    /**
     * @throws \Exception
     */
    public function testUpdate()
    {
        $list = $this->cacheSettingsEndpoint->getList($this->testDomain);
        foreach ($list['list'] as $item) {
            if ($item['path'] == $this->testPath) {
                $res = $this->cacheSettingsEndpoint->update(
                    $this->testDomain, $item['id'],
                    new \DateTime($item['modified']),
                    $this->testPath2,
                    500
                );
                var_export($res);
                $this->assertArrayHasKey('targetObject', $res);
                $this->assertEquals(1, count($res['targetObject']));
                $this->assertArrayHasKey('path', $res['targetObject'][0]);
                $this->assertEquals($this->testPath2, $res['targetObject'][0]['path']);
                $this->assertArrayHasKey('ttl', $res['targetObject'][0]);
                $this->assertEquals(500, $res['targetObject'][0]['ttl']);
            }
        }
    }

    /**
     *
     */
    public function testGetList()
    {
        $result = $this->cacheSettingsEndpoint->getList($this->testDomain);
        $this->verifyListResult($result);
        var_dump($result);
        var_export(array_keys($result['list'][0]));
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDelete()
    {
        $list = $this->cacheSettingsEndpoint->getList($this->testDomain);
        foreach ($list['list'] as $item) {
            if ($item['path'] == $this->testPath
                || $item['path'] == $this->testPath2
            ) {
                $result = $this->cacheSettingsEndpoint->delete(
                    $this->testDomain,
                    $item['id'],
                    new \DateTime($item['modified'])
                );
                $this->verifyNoError($result);
            }
        }
    }
}
