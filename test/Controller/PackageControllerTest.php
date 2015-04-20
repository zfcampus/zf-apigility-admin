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
use ZF\Apigility\Admin\Controller\PackageController;
use ZF\ContentNegotiation\ControllerPlugin\BodyParam;
use ZF\ContentNegotiation\ControllerPlugin\BodyParams;
use Zend\Mvc\MvcEvent;
use ZF\ContentNegotiation\ParameterDataContainer;
use Zend\Mvc\Router\RouteMatch;

class PackageControllerTest extends TestCase
{
    public function setUp()
    {
        // Seed with symlink path for zfdeploy.php
        $this->controller = new PackageController('vendor/bin/zfdeploy.php');
        $this->plugins = new ControllerPluginManager();
        $this->plugins->setService('bodyParam', new BodyParam());
        $this->plugins->setService('bodyParams', new BodyParams());
        $this->controller->setPluginManager($this->plugins);
    }

    public function invalidRequestMethods()
    {
        return array(
            array('patch'),
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
        $result = $this->controller->indexAction();
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblemResponse', $result);
        $apiProblem = $result->getApiProblem();
        $this->assertEquals(405, $apiProblem->status);
    }


    public function testProcessPostRequestReturnsToken()
    {
        $request = new Request();
        $request->setMethod('post');

        $parameters = new ParameterDataContainer();
        $parameters->setBodyParam('format', 'ZIP');
        $event = new MvcEvent();
        $event->setParam('ZFContentNegotiationParameterData', $parameters);

        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');

        $this->controller->setRequest($request);
        $this->controller->setEvent($event);

        $cwd = getcwd();
        chdir(__DIR__ . '/TestAsset');
        $result = $this->controller->indexAction();
        chdir($cwd);

        $this->assertInternalType('array', $result);
        $this->assertTrue(isset($result['token']));
        $this->assertTrue(isset($result['format']));
        $package = sys_get_temp_dir() . '/apigility_' . $result['token'] . '.' . $result['format'];
        $this->assertTrue(file_exists($package));

        return $result;
    }

    /**
     * @depends testProcessPostRequestReturnsToken
     */
    public function testProcessGetRequestReturnsFile(array $data)
    {
        $request = new Request();
        $request->setMethod('get');
        $request->getQuery()->set('format', $data['format']);
        $request->getQuery()->set('token', $data['token']);

        $this->controller->setRequest($request);

        $package = sys_get_temp_dir() . '/apigility_' . $data['token'] . '.' . $data['format'];
        $content = file_get_contents($package);

        $response = $this->controller->indexAction();

        $this->assertTrue($response->isSuccess());
        $this->assertEquals($content, $response->getRawBody());
        $this->assertEquals('application/octet-stream', $response->getHeaders()->get('Content-Type')->getFieldValue());
        $this->assertEquals(strlen($content), $response->getHeaders()->get('Content-Length')->getFieldValue());

        // Removal of file only happens during destruct
        $this->controller->__destruct();
        $this->assertFalse(file_exists($package));
    }
}
