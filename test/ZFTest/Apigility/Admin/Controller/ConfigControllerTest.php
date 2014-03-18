<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Controller;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request;
use Zend\Mvc\Controller\PluginManager as ControllerPluginManager;
use Zend\Stdlib\Parameters;
use ZF\Apigility\Admin\Controller\ConfigController;
use ZF\Configuration\ConfigResource;
use ZF\ContentNegotiation\ControllerPlugin\BodyParams;

class ConfigControllerTest extends TestCase
{
    public function setUp()
    {
        $this->file = tempnam(sys_get_temp_dir(), 'zfconfig');
        file_put_contents($this->file, '<' . "?php\nreturn array();");

        $this->writer         = new TestAsset\ConfigWriter();
        $this->configResource = new ConfigResource(array(), $this->file, $this->writer);
        $this->controller     = new ConfigController($this->configResource);

        $this->plugins = new ControllerPluginManager();
        $this->plugins->setService('BodyParams', new BodyParams());
        $this->controller->setPluginManager($this->plugins);
    }

    public function tearDown()
    {
        unlink($this->file);
    }

    public function invalidRequestMethods()
    {
        return array(
            array('post'),
            array('put'),
            array('delete'),
        );
    }

    /**
     * @dataProvider invalidRequestMethods
     */
    public function testProcessWithInvalidRequestMethodReturnsApiProblemResponse($method)
    {
        $request = new Request();
        $request->setMethod($method);
        $this->controller->setRequest($request);
        $result = $this->controller->processAction();
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblemResponse', $result);
        $apiProblem = $result->getApiProblem();
        $this->assertEquals(405, $apiProblem->status);
    }

    public function testProcessGetRequestWithZfCampusMediaTypeReturnsFullConfiguration()
    {
        $config = array(
            'foo' => 'FOO',
            'bar' => array(
                'baz' => 'bat',
            ),
            'baz' => 'BAZ',
        );
        $configResource = new ConfigResource($config, $this->file, $this->writer);
        $controller     = new ConfigController($configResource);
        $controller->setPluginManager($this->plugins);

        $request = new Request();
        $request->setMethod('get');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/vnd.zfcampus.v1.config+json');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.zfcampus.v1.config+json');
        $controller->setRequest($request);

        $result = $controller->processAction();
        $this->assertInternalType('array', $result);
        $this->assertEquals($config, $result);
    }

    public function testProcessGetRequestWithGenericJsonMediaTypeReturnsFlattenedConfiguration()
    {
        $config = array(
            'foo' => 'FOO',
            'bar' => array(
                'baz' => 'bat',
            ),
            'baz' => 'BAZ',
        );
        $configResource = new ConfigResource($config, $this->file, $this->writer);
        $controller     = new ConfigController($configResource);
        $controller->setPluginManager($this->plugins);

        $request = new Request();
        $request->setMethod('get');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $controller->setRequest($request);

        $result = $controller->processAction();
        $this->assertInternalType('array', $result);

        $expected = array(
            'foo'     => 'FOO',
            'bar.baz' => 'bat',
            'baz'     => 'BAZ',
        );
        $this->assertEquals($expected, $result);
    }

    public function testProcessPatchRequestWithZfCampusMediaTypeReturnsUpdatedConfigurationKeys()
    {
        $config = array(
            'foo' => 'FOO',
            'bar' => array(
                'baz' => 'bat',
            ),
            'baz' => 'BAZ',
        );
        $configResource = new ConfigResource($config, $this->file, $this->writer);
        $controller     = new ConfigController($configResource);
        $controller->setPluginManager($this->plugins);

        $request = new Request();
        $request->setMethod('patch');
        $request->setContent(json_encode(array(
            'bar' => array(
                'baz' => 'UPDATED',
            ),
            'baz' => 'UPDATED',
        )));
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/vnd.zfcampus.v1.config+json');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.zfcampus.v1.config+json');
        $controller->setRequest($request);

        $result = $controller->processAction();
        $this->assertInternalType('array', $result);

        $expected = array(
            'bar' => array(
                'baz' => 'UPDATED',
            ),
            'baz' => 'UPDATED',
        );
        $this->assertEquals($expected, $result);
    }

    public function testProcessPatchRequestWithGenericJsonMediaTypeReturnsUpdatedConfigurationKeys()
    {
        $config = array(
            'foo' => 'FOO',
            'bar' => array(
                'baz' => 'bat',
            ),
            'baz' => 'BAZ',
        );
        $configResource = new ConfigResource($config, $this->file, $this->writer);
        $controller     = new ConfigController($configResource);
        $controller->setPluginManager($this->plugins);

        $request = new Request();
        $request->setMethod('patch');
        $request->setPost(new Parameters(array(
            'bar.baz' => 'UPDATED',
            'baz' => 'UPDATED',
        )));
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $controller->setRequest($request);

        $result = $controller->processAction();
        $this->assertInternalType('array', $result);

        $expected = array(
            'bar.baz' => 'UPDATED',
            'baz' => 'UPDATED',
        );
        $this->assertEquals($expected, $result);
    }
}
