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
use ZF\Apigility\Admin\Controller\AuthenticationController;
use ZF\Apigility\Admin\Model\AuthenticationModel;
use ZF\Configuration\ConfigResource;
use Zend\Config\Writer\PhpArray as ConfigWriter;
use ZF\ContentNegotiation\ParameterDataContainer;

class AuthenticationControllerTest extends TestCase
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

        $moduleModel = $this->getMockBuilder('ZF\Apigility\Admin\Model\ModuleModel')
                            ->disableOriginalConstructor()
                            ->getMock();

        $model = new AuthenticationModel($global, $local, $moduleModel);
        $this->controller = new AuthenticationController($model);

        $this->plugins = new ControllerPluginManager();
        $this->plugins->setService('bodyParams', new BodyParams());
        $this->plugins->setService('bodyParam', new BodyParam());
        $this->plugins->setService('params', new Params());
        $this->controller->setPluginManager($this->plugins);

        $this->routeMatch = new RouteMatch(array());
        $this->routeMatch->setMatchedRouteName('zf-apigility/api/authentication');
        $this->event = new MvcEvent();
        $this->event->setRouteMatch($this->routeMatch);

        $config = require __DIR__ . '/../../config/module.config.php';
        $router = new SimpleRouteStack();
        $router->addRoute(
            'zf-apigility/api/authentication',
            $config['router']['routes']['zf-apigility']['child_routes']['api']['child_routes']['authentication']
        );
        $this->event->setRouter($router);
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
            array('patch')
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

        $result = $this->controller->authenticationAction();
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

        $params = array(
            'authentication_adapter' => 'testbasic'
        );
        $this->routeMatch = new RouteMatch($params);
        $this->event->setRouteMatch($this->routeMatch);

        $result = $this->controller->authenticationAction();

        $this->assertInstanceOf('ZF\ContentNegotiation\ViewModel', $result);
        $payload = $result->getVariable('payload');
        $this->assertInstanceOf('ZF\Hal\Entity', $payload);

        $metadata = $payload->entity;
        $this->assertEquals('testbasic', $metadata['name']);
    }

    public function testGetAllAuthenticationRequest()
    {
        $request = new Request();
        $request->setMethod('get');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.apigility.v2+json');
        $this->controller->setRequest($request);

        $result = $this->controller->authenticationAction();

        $this->assertInstanceOf('ZF\ContentNegotiation\ViewModel', $result);
        $payload = $result->getVariable('payload');
        $this->assertInstanceOf('ZF\Hal\Collection', $payload);
        $collection = $payload->getCollection();
        foreach ($collection as $entity) {
            $this->assertInstanceOf('ZF\Hal\Entity', $entity);
        }
        $this->assertEquals(4, count($collection));
    }

    /**
     * Data for POST requests
     */
    public function postRequestData()
    {
        $data = array(
            array(
                array(
                    'name'     => 'test',
                    'type'     => 'basic',
                    'realm'    => 'api',
                    'htpasswd' => __DIR__ . '/TestAsset/Auth2/config/autoload/htpasswd'
                ),
            ),
            array(
                array(
                    'name'           => 'test2',
                    'type'           => 'digest',
                    'realm'          => 'api',
                    'nonce_timeout'  => '3600',
                    'digest_domains' => null,
                    'htdigest'       => __DIR__ . '/TestAsset/Auth2/config/autoload/htdigest'
                ),
            )
        );
        if (extension_loaded('pdo_sqlite')) {
            $data[] = array(
                array(
                    'name'            => 'test3',
                    'type'            => 'oauth2',
                    'oauth2_type'     => 'pdo',
                    'oauth2_route'    => '/oauth-pdo',
                    'oauth2_dsn'      => 'sqlite:' . __DIR__ . '/TestAsset/Auth2/config/autoload/db.sqlite',
                    'oauth2_username' => null,
                    'oauth2_password' => null,
                    'oauth2_options'  => null
                )
            );
        }
        if (extension_loaded('mongo')) {
            $data[] = array(
                array(
                    'name'                => 'test4',
                    'type'                => 'oauth2',
                    'oauth2_type'         => 'mongo',
                    'oauth2_route'        => '/oauth-mongo',
                    'oauth2_dsn'          => 'mongodb://localhost',
                    'oauth2_database'     => 'zf-apigility-admin-test',
                    'oauth2_locator_name' => null,
                    'oauth2_options'      => null
                )
            );
        }
        return $data;
    }

    /**
     * @dataProvider postRequestData
     */
    public function testPostAuthenticationRequest($postData)
    {
        $request = new Request();
        $request->setMethod('post');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.apigility.v2+json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $this->controller->setRequest($request);

        $parameters = new ParameterDataContainer();
        $parameters->setBodyParams($postData);
        $this->event->setParam('ZFContentNegotiationParameterData', $parameters);


        $result = $this->controller->authenticationAction();
        $this->assertInstanceOf('ZF\ContentNegotiation\ViewModel', $result);
        $payload = $result->getVariable('payload');
        $this->assertInstanceOf('ZF\Hal\Entity', $payload);
        $self = $payload->getLinks()->get('self');
        $this->assertEquals('zf-apigility/api/authentication', $self->getRoute());
        $params = $self->getRouteParams();
        $this->assertEquals($postData['name'], $params['authentication_adapter']);
    }

    /**
     * @dataProvider postRequestData
     */
    public function testPutAuthenticationRequest($postData)
    {
        $request = new Request();
        $request->setMethod('put');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.apigility.v2+json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $this->controller->setRequest($request);

        $parameters = new ParameterDataContainer();
        $parameters->setBodyParams($postData);
        $this->event->setParam('ZFContentNegotiationParameterData', $parameters);

        $params = array(
            'authentication_adapter' => 'testbasic'
        );
        $this->routeMatch = new RouteMatch($params);
        $this->event->setRouteMatch($this->routeMatch);

        $result = $this->controller->authenticationAction();
        $this->assertInstanceOf('ZF\ContentNegotiation\ViewModel', $result);
        $payload = $result->getVariable('payload');
        $this->assertInstanceOf('ZF\Hal\Entity', $payload);
        $self = $payload->getLinks()->get('self');
        $this->assertEquals('zf-apigility/api/authentication', $self->getRoute());
        $params = $self->getRouteParams();
        $this->assertEquals('testbasic', $params['authentication_adapter']);

        $metadata = $payload->entity;
        $this->assertEmpty(array_diff_key($metadata, $postData));
    }

    public function testDeleteAuthenticationRequest()
    {
        $request = new Request();
        $request->setMethod('delete');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.apigility.v2+json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $this->controller->setRequest($request);

        $params = array(
            'authentication_adapter' => 'testbasic'
        );
        $this->routeMatch = new RouteMatch($params);
        $this->event->setRouteMatch($this->routeMatch);

        $result = $this->controller->authenticationAction();
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $result);
        $this->assertEquals(204, $result->getStatusCode());
    }

    public function testGetAuthenticationMapRequest()
    {
        $request = new Request();
        $request->setMethod('get');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.apigility.v2+json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $request->getQuery()->set('version', 1);
        $this->controller->setRequest($request);

        $params = array(
            'name' => 'Status'
        );
        $this->routeMatch = new RouteMatch($params);
        $this->routeMatch->setMatchedRouteName('zf-apigility/api/module/authentication');
        $this->event->setRouteMatch($this->routeMatch);

        $result = $this->controller->mappingAction();
        $this->assertInstanceOf('ZF\ContentNegotiation\ViewModel', $result);
        $config = require $this->globalFile;
        $this->assertEquals(
            $config['zf-mvc-auth']['authentication']['map']['Status\V1'],
            $result->getVariable('authentication')
        );
    }

    public function testGetWrongAuthenticationMapRequest()
    {
        $request = new Request();
        $request->setMethod('get');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.apigility.v2+json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $request->getQuery()->set('version', 1);
        $this->controller->setRequest($request);

        $params = array(
            'name' => 'Status2'
        );
        $this->routeMatch = new RouteMatch($params);
        $this->routeMatch->setMatchedRouteName('zf-apigility/api/module/authentication');
        $this->event->setRouteMatch($this->routeMatch);

        $result = $this->controller->mappingAction();
        $this->assertInstanceOf('ZF\ContentNegotiation\ViewModel', $result);
        $this->assertFalse($result->getVariable('authentication'));
    }

    public function testAddAuthenticationMapRequest()
    {
        $request = new Request();
        $request->setMethod('put');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.apigility.v2+json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $this->controller->setRequest($request);

        $parameters = new ParameterDataContainer();
        $parameters->setBodyParams(array(
            'authentication' => 'testoauth2pdo'
        ));
        $this->event->setParam('ZFContentNegotiationParameterData', $parameters);

        $params = array(
            'name' => 'Foo'
        );
        $this->routeMatch = new RouteMatch($params);
        $this->routeMatch->setMatchedRouteName('zf-apigility/api/module/authentication');
        $this->event->setRouteMatch($this->routeMatch);

        $result = $this->controller->mappingAction();
        $this->assertInstanceOf('ZF\ContentNegotiation\ViewModel', $result);
        $this->assertEquals('testoauth2pdo', $result->getVariable('authentication'));
    }

    public function testUpdateAuthenticationMapRequest()
    {
        $request = new Request();
        $request->setMethod('put');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.apigility.v2+json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $request->getQuery()->set('version', 2);
        $this->controller->setRequest($request);

        $parameters = new ParameterDataContainer();
        $parameters->setBodyParams(array(
            'authentication' => 'testoauth2mongo'
        ));
        $this->event->setParam('ZFContentNegotiationParameterData', $parameters);

        $params = array(
            'name' => 'Status'
        );
        $this->routeMatch = new RouteMatch($params);
        $this->routeMatch->setMatchedRouteName('zf-apigility/api/module/authentication');
        $this->event->setRouteMatch($this->routeMatch);

        $result = $this->controller->mappingAction();
        $this->assertInstanceOf('ZF\ContentNegotiation\ViewModel', $result);
        $this->assertEquals('testoauth2mongo', $result->getVariable('authentication'));
    }

    public function testRemoveAuthenticationMapRequest()
    {
        $request = new Request();
        $request->setMethod('delete');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.apigility.v2+json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $request->getQuery()->set('version', 1);
        $this->controller->setRequest($request);

        $params = array(
            'name' => 'Status'
        );
        $this->routeMatch = new RouteMatch($params);
        $this->routeMatch->setMatchedRouteName('zf-apigility/api/module/authentication');
        $this->event->setRouteMatch($this->routeMatch);

        $result = $this->controller->mappingAction();
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $result);
        $this->assertEquals(204, $result->getStatusCode());
    }
}
