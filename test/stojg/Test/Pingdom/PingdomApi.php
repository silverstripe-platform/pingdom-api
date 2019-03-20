<?php

use stojg\Pingdom\Api;
use stojg\Pingdom\MissingCredentialsException;
use stojg\Pingdom\MissingParameterException;

class UnitTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Api
     */
    protected $pingdom;

    protected $default_check = [
        'name' => 'name',
        'host' => 'host',
        'url' => 'url',
    ];

    public function setUp()
    {
        $this->pingdom = new Api('username', 'password', 'api_key');
    }

    public function tearDown()
    {
        $this->default_check = null;
        $this->pingdom = null;
    }

    /**
     * Test the Pingdom username is required.
     */
    public function testMissingCredentialsUsername()
    {
        $this->expectException(MissingCredentialsException::class);
        $api = new Api(null, 'password', 'api_key');
        $api->getChecks();
    }

    /**
     * Test the Pingdom password is required.
     */
    public function testMissingCredentialsPassword()
    {
        $this->expectException(MissingCredentialsException::class);
        $api = new Api('username', null, 'api_key');
        $api->getChecks();
    }

    /**
     * Test the Pingdom API key is required.
     */
    public function testMissingCredentialsApiKey()
    {
        $this->expectException(MissingCredentialsException::class);
        $api = new Api('username', 'password', null);
        $api->getChecks();
    }

    /**
     * Test getCheck() requires the $check_id parameter.
     */
    public function testMissingParameterGetCheck()
    {
        $this->expectException(MissingParameterException::class);
        $this->pingdom->getCheck(null);
    }

    /**
     * Test addCheck() requires the $check parameter.
     */
    public function testMissingParameterAddCheckNull()
    {
        $this->expectException(MissingParameterException::class);
        $this->pingdom->addCheck(['name' => null, 'host' => null, 'url' => null]);
    }

    /**
     * Test addCheck() requires the name index of the $check parameter.
     */
    public function testMissingParameterAddCheckName()
    {
        $this->expectException(MissingParameterException::class);
        $check = $this->default_check;
        $check['name'] = null;
        $this->pingdom->addCheck($check);
    }

    /**
     * Test addCheck() requires the host index of the $check parameter.
     */
    public function testMissingParameterAddCheckHost()
    {
        $this->expectException(MissingParameterException::class);
        $check = $this->default_check;
        $check['host'] = null;
        $this->pingdom->addCheck($check);
    }

    /**
     * Test addCheck() requires the url index of the $check parameter.
     */
    public function testMissingParameterAddCheckUrl()
    {
        $this->expectException(MissingParameterException::class);
        $check = $this->default_check;
        $check['url'] = null;
        $this->pingdom->addCheck($check);
    }

    /**
     * Test pauseCheck() requires the $check_id parameter.
     */
    public function testMissingParameterPauseCheck()
    {
        $this->expectException(MissingParameterException::class);
        $this->pingdom->pauseCheck(null);
    }

    /**
     * Test unpauseCheck() requires the $check_id parameter.
     */
    public function testMissingParametersUnpauseCheck()
    {
        $this->expectException(MissingParameterException::class);
        $this->pingdom->unpauseCheck(null);
    }

    /**
     * Test pauseChecks() requires the $check_ids parameter.
     */
    public function testMissingParametersPauseChecks()
    {
        $this->expectException(MissingParameterException::class);
        $this->pingdom->pauseChecks(null);
    }

    /**
     * Test unpauseChecks() require the $check_ids parameter.
     */
    public function testMissingParametersUnpauseChecks()
    {
        $this->expectException(MissingParameterException::class);
        $this->pingdom->unpauseChecks(null);
    }

    /**
     * Test modifyCheck() requires the $check_id parameter.
     */
    public function testMissingParametersModifyCheckCheckId()
    {
        $this->expectException(MissingParameterException::class);
        $this->pingdom->modifyCheck(null, ['parameter' => 'value']);
    }

    /**
     * Test modifyCheck() requires the $parameters parameter.
     */
    public function testMissingParametersModifyCheckParameters()
    {
        $this->expectException(MissingParameterException::class);
        $this->pingdom->modifyCheck(12345678, null);
    }

    /**
     * Test modifyChecks() requires the $check_id parameter.
     */
    public function testMissingParametersModifyChecksCheckId()
    {
        $this->expectException(MissingParameterException::class);
        $this->pingdom->modifyChecks(null, ['parameter' => 'value']);
    }

    /**
     * Test modifyChecks() requires the $parameters parameter.
     */
    public function testMissingParametersModifyChecksParameters()
    {
        $this->expectException(MissingParameterException::class);
        $this->pingdom->modifyChecks(12345678, null);
    }

    /**
     * Test modifyAllChecks() requires the $parameters parameter.
     */
    public function testMissingParametersModifyAllChecks()
    {
        $this->expectException(MissingParameterException::class);
        $this->pingdom->modifyAllChecks(null);
    }

    /**
     * Test removeCheck() requires the $check_id parameter.
     */
    public function testMissingParametersRemoveCheck()
    {
        $this->expectException(MissingParameterException::class);
        $this->pingdom->removeCheck(null);
    }

    /**
     * Test getAnalysis() requires the $check_id parameter.
     */
    public function testMissingParametersGetAnalysis()
    {
        $this->expectException(MissingParameterException::class);
        $this->pingdom->getAnalysis(null);
    }

    /**
     * Test getRawAnalysis() requires the $check_id parameter.
     */
    public function testMissingParametersGetRawAnalysisCheckId()
    {
        $this->expectException(MissingParameterException::class);
        $this->pingdom->getRawAnalysis(null, 12345678);
    }

    /**
     * Test getRawAnalysis() requires the $analysis_id parameter.
     */
    public function testMissingParametersGetRawAnalysisAnalysisId()
    {
        $this->expectException(MissingParameterException::class);
        $this->pingdom->getRawAnalysis(12345678, null);
    }

    /**
     * Test ensureParameters() requires the $parameters parameter.
     */
    public function testMissingParametersEnsureParameters()
    {
        $this->expectException(MissingParameterException::class);
        $this->pingdom->ensureParameters(null, __METHOD__);
    }

    /**
     * Test ensureParameters() requires the $method parameter.
     */
    public function testMissingParametersEnsureMethod()
    {
        $this->expectException(MissingParameterException::class);
        $this->pingdom->ensureParameters([], null);
    }

    /**
     * Test ensureParameters() handles empty parameter values correctly.
     */
    public function testMissingParametersEnsureParametersEmptyValues()
    {
        $parameters = [
            'bool' => false,
            'int' => 0,
            'float' => 0.0,
            'string' => '',
            'array' => [],
        ];

        $this->pingdom->ensureParameters($parameters, __METHOD__);
    }

    /**
     * Test buildRequestUrl() handles query parameters correctly.
     */
    public function testBuildRequestUrl()
    {
        ini_set('arg_separator.output', '&amp;');
        $parameters = [
            'bool_true' => true,
            'bool_false' => false,
            'int_true' => 1,
            'int_false' => 0,
        ];
        $coerced = $this->pingdom->buildRequestUrl('resource', $parameters);
        $expected = 'https://api.pingdom.com/api/2.1/resource?bool_true=true&bool_false=false&int_true=1&int_false=0';
        $this->assertSame($coerced, $expected);
    }
}
