<?php
declare(strict_types=1);

namespace Myracloud\Tests\Endpoint;

use Myracloud\WebApi\Endpoint\Redirect;

/**
 * Class RedirectTest
 *
 * @package Myracloud\Tests\Endpoint
 */
class RedirectTest extends AbstractEndpointTest
{
    /**
     * @var Redirect
     */
    protected $redirectEndpoint;

    protected $testData = [
        'create' => [
            'source'        => '/test_source',
            'destination'   => '/test_dest',
            'type'          => Redirect::REDIRECT_TYPE_REDIRECT,
            'subDomainName' => self::TESTDOMAIN . '.',
            'matchingType'  => Redirect::MATCHING_TYPE_PREFIX,
        ],
        'update' => [
            'source'        => '/test_source_changed',
            'destination'   => '/test_destination_changed',
            'type'          => Redirect::REDIRECT_TYPE_REDIRECT,
            'subDomainName' => self::TESTDOMAIN . '.',
            'matchingType'  => Redirect::MATCHING_TYPE_PREFIX,
        ],
    ];

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->redirectEndpoint = $this->Api->getRedirectEndpoint();
        $this->assertThat($this->redirectEndpoint, $this->isInstanceOf('Myracloud\WebApi\Endpoint\Redirect'));
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testUpdate()
    {
        $this->testCreate();
        $list = $this->redirectEndpoint->getList(self::TESTDOMAIN);
        foreach ($list['list'] as $item) {
            if ($item['source'] == $this->testData['create']['source']) {
                $result = $this->redirectEndpoint->update(
                    self::TESTDOMAIN, $item['id'],
                    new \DateTime($item['modified']),
                    $this->testData['update']['source'],
                    $this->testData['update']['destination']
                );

                $this->verifyNoError($result);
                $this->verifyTargetObject($result, 'DnsRedirectVO');
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
        $result = $this->redirectEndpoint->create(
            self::TESTDOMAIN,
            $this->testData['create']['source'],
            $this->testData['create']['destination']
        );
        $this->verifyNoError($result);
        $this->verifyTargetObject($result, 'DnsRedirectVO');
        $this->verifyFields($result['targetObject'][0], $this->testData['create']);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDelete()
    {
        $list = $this->redirectEndpoint->getList(self::TESTDOMAIN);
        foreach ($list['list'] as $item) {
            if (
                $item['source'] == $this->testData['create']['source']
                || $item['source'] == $this->testData['update']['source']
            ) {
                $result = $this->redirectEndpoint->delete(
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
        $result = $this->redirectEndpoint->getList(self::TESTDOMAIN);
        $this->verifyListResult($result);
        var_dump($result);
    }
}

