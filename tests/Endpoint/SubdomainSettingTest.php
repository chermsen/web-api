<?php
declare(strict_types=1);

namespace Myracloud\Tests\Endpoint;

use Myracloud\WebApi\Endpoint\SubdomainSetting;

class SubdomainSettingTest extends AbstractEndpointTest
{
    /** @var SubdomainSetting */
    protected $subdomainSettingEndpoint;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->subdomainSettingEndpoint = $this->Api->getSubdomainSettingsEndpoint();
        $this->assertThat($this->subdomainSettingEndpoint, $this->isInstanceOf('Myracloud\WebApi\Endpoint\SubdomainSetting'));
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGet()
    {
        $result = $this->subdomainSettingEndpoint->get(self::TESTDOMAIN);
        var_dump($result);
        $this->verifyNoError($result);
        $this->verifyTargetObject($result, 'SubdomainSettingVO');
    }

    public function testSet()
    {
        $data = [
            'cdn' => true,
        ];

        $result = $this->subdomainSettingEndpoint->set(self::TESTDOMAIN, $data);
        var_dump($result);
        $this->verifyNoError($result);
    }


}
