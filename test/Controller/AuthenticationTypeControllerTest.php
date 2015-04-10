<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Controller;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request;
use Zend\Mvc\Controller\PluginManager as ControllerPluginManager;
use Zend\Mvc\Controller\Plugin\Params;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\SimpleRouteStack;
use ZF\ContentNegotiation\ControllerPlugin\BodyParams;
use ZF\ContentNegotiation\ControllerPlugin\BodyParam;
use ZF\Apigility\Admin\Controller\AuthenticationTypeController;
use ZF\MvcAuth\Authentication\DefaultAuthenticationListener as AuthListener;

use ZF\Configuration\ConfigResource;
use Zend\Config\Writer\PhpArray as ConfigWriter;
use ZF\ContentNegotiation\ParameterDataContainer;

class AuthenticationTypeControllerTest extends TestCase
{
    public function setUp()
    {
        $this->globalFile = __DIR__ . '/TestAsset/Auth2/config/autoload/global.php';
        $this->localFile  = __DIR__ . '/TestAsset/Auth2/config/autoload/local.php';
        copy($this->globalFile . '.dist', $this->globalFile);
        copy($this->localFile . '.dist', $this->localFile);

        $writer = new ConfigWriter();
        $global = new ConfigResource(require $this->globalFile, $this->globalFile, $writer);
        $local  = new ConfigResource(require $this->localFile, $this->localFile, $writer);

        $model = new AuthListener();
        $config = require $this->localFile;
        $model->addAuthenticationTypes($config['zf-mvc-auth']['authentication']);
        $this->controller = new AuthenticationTypeController($model);

        $this->routeMatch = new RouteMatch(array());
        $this->routeMatch->setMatchedRouteName('zf-apigility/api/authentication-type');
        $this->event = new MvcEvent();
        $this->event->setRouteMatch($this->routeMatch);

        $this->controller->setEvent($this->event);
    }

    public function tearDown()
    {
        unlink($this->localFile);
        unlink($this->globalFile);
    }

    public function invalidRequestMethods()
    {
        return array(
            array('post', 'put', 'patch', 'delete')
        );
    }

    /**
     * @dataProvider invalidRequestMethods
     */
    public function testProcessWithInvalidRequestMethodReturnsApiProblemModel($method)
    {
        $request = new Request();
        $request->setMethod($method);
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.apigility.v2+json');
        $this->controller->setRequest($request);

        $result = $this->controller->authTypeAction();
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblemResponse', $result);
        $apiProblem = $result->getApiProblem();
        $this->assertEquals(405, $apiProblem->status);
    }


    public function testGetAuthenticationRequest()
    {
        $request = new Request();
        $request->setMethod('get');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.apigility.v2+json');
        $this->controller->setRequest($request);

        $result = $this->controller->authTypeAction();

        $this->assertInstanceOf('ZF\ContentNegotiation\ViewModel', $result);
        $config = require $this->localFile;
        $adapters = array_keys($config['zf-mvc-auth']['authentication']['adapters']);
        $this->assertEquals($adapters, $result->getVariable('auth-types'));
    }
}
