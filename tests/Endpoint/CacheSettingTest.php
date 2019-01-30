<?php
declare(strict_types=1);

namespace Myracloud\Tests\Endpoint;

use Myracloud\WebApi\Endpoint\CacheSetting;

/**
 * Class CacheSettingTest
 *
 * @package Myracloud\WebApi\Endpoint
 */
class CacheSettingTest extends AbstractEndpointTest
{
    /** @var CacheSetting */
    protected $cacheSettingsEndpoint;


    protected $testData = [
        'create' => [
            'path'        => '/testPath',
            'ttl'         => 300,
            'notFoundTtl' => 60,
            'type'        => CacheSetting::MATCHING_TYPE_PREFIX,
            'enforce'     => false,
            'sort'        => 0,
        ],
        'update' => [
            'path'        => '/testPathUpdate',
            'ttl'         => 333,
            'notFoundTtl' => 60,
            'type'        => CacheSetting::MATCHING_TYPE_PREFIX,
            'enforce'     => false,
            'sort'        => 0,
        ],
    ];

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
    public function testUpdate()
    {
        $this->testCreate();
        $list = $this->cacheSettingsEndpoint->getList(self::TESTDOMAIN);
        foreach ($list['list'] as $item) {
            if ($item['path'] == $this->testData['create']['path']) {
                $result = $this->cacheSettingsEndpoint->update(
                    self::TESTDOMAIN,
                    $item['id'],
                    new \DateTime($item['modified']),
                    $this->testData['update']['path'],
                    $this->testData['update']['ttl']
                );
                var_export($result);
                $this->verifyNoError($result);
                $this->verifyTargetObject($result, 'CacheSettingVO');
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
        $result = $this->cacheSettingsEndpoint->create(
            self::TESTDOMAIN,
            $this->testData['create']['path'],
            $this->testData['create']['ttl']
        );

        $this->verifyNoError($result);
        $this->verifyTargetObject($result, 'CacheSettingVO');
        $this->verifyFields($result['targetObject'][0], $this->testData['create']);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDelete()
    {
        $list = $this->cacheSettingsEndpoint->getList(self::TESTDOMAIN);
        foreach ($list['list'] as $item) {
            if (
                $item['path'] == $this->testData['create']['path']
                || $item['path'] == $this->testData['update']['path']
            ) {
                $result = $this->cacheSettingsEndpoint->delete(
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
        $result = $this->cacheSettingsEndpoint->getList(self::TESTDOMAIN);
        $this->verifyListResult($result);
        var_dump($result);
    }
}
