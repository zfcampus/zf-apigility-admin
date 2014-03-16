<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Controller;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Controller\PluginManager;
use ZF\Apigility\Admin\Controller\InputFilterController;
use ZF\Apigility\Admin\Model\InputFilterModel;
use ZF\Configuration\ResourceFactory as ConfigResourceFactory;
use ZF\Configuration\ModuleUtils;
use Zend\Config\Writer\PhpArray;
use ZF\ContentNegotiation\ParameterDataContainer;

class InputFilterControllerTest extends TestCase
{
    public function setUp()
    {
        require_once __DIR__ . '/../Model/TestAsset/module/InputFilter/Module.php';
        $modules = array(
            'InputFilter' => new \InputFilter\Module()
        );

        $this->moduleManager = $this->getMockBuilder('Zend\ModuleManager\ModuleManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $this->writer        = new PhpArray();
        $moduleUtils         = new ModuleUtils($this->moduleManager);
        $this->configFactory = new ConfigResourceFactory($moduleUtils, $this->writer);
        $this->model         = new InputFilterModel($this->configFactory);
        $this->controller    = new InputFilterController($this->model);

        $this->basePath      = __DIR__ . '/../Model/TestAsset/module/InputFilter/config';
        $this->config        = include $this->basePath . '/module.config.php';

        copy($this->basePath . '/module.config.php', $this->basePath . '/module.config.php.old');
    }

    public function tearDown()
    {
        copy($this->basePath .'/module.config.php.old', $this->basePath . '/module.config.php');
        unlink($this->basePath . '/module.config.php.old');
    }

    public function testGetInputFilters()
    {
        $request = new Request();
        $request->setMethod('get');
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');

        $module     = 'InputFilter';
        $controller = 'InputFilter\V1\Rest\Foo\Controller';
        $params = array(
            'name' => $module,
            'controller_service_name' => $controller
        );
        $routeMatch = new RouteMatch($params);
        $routeMatch->setMatchedRouteName('zf-apigility-admin/api/module/rest-service/rest_input_filter');
        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);

        $this->controller->setRequest($request);
        $this->controller->setEvent($event);

        $result = $this->controller->indexAction();
        $this->assertInstanceOf('ZF\ContentNegotiation\ViewModel', $result);
        $payload = $result->payload;
        $this->assertInstanceOf('ZF\Hal\Collection', $payload);
        $collection = $payload->getCollection();
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\InputFilterCollection', $collection);
        $inputFilter = $collection->dequeue();
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\InputFilterEntity', $inputFilter);

        $inputFilterKey = $this->config['zf-content-validation'][$controller]['input_filter'];
        $expected = $this->config['input_filter_specs'][$inputFilterKey];
        $expected['input_filter_name'] = $inputFilterKey;
        $this->assertEquals($expected, $inputFilter->getArrayCopy());
    }

    public function testGetInputFilter()
    {
        $request = new Request();
        $request->setMethod('get');
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');

        $module     = 'InputFilter';
        $controller = 'InputFilter\V1\Rest\Foo\Controller';
        $validator  = 'InputFilter\V1\Rest\Foo\Validator';
        $params = array(
            'name' => $module,
            'controller_service_name' => $controller,
            'input_filter_name' => $validator,
        );
        $routeMatch = new RouteMatch($params);
        $routeMatch->setMatchedRouteName('zf-apigility-admin/api/module/rest-service/rest_input_filter');
        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);

        $this->controller->setRequest($request);
        $this->controller->setEvent($event);

        $result = $this->controller->indexAction();
        $this->assertInstanceOf('ZF\ContentNegotiation\ViewModel', $result);
        $payload = $result->payload;
        $this->assertInstanceOf('ZF\Hal\Entity', $payload);
        $entity = $payload->entity;
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\InputFilterEntity', $entity);

        $expected = $this->config['input_filter_specs'][$validator];
        $expected['input_filter_name'] = $validator;
        $this->assertEquals($expected, $entity->getArrayCopy());
    }

    public function testAddInputFilter()
    {
        $inputFilter = array(
            array(
                'name' => 'bar',
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'options' => array(
                            'type' => 127,
                        ),
                    ),
                    array(
                        'name' => 'Digits',
                    ),
                ),
            ),
        );

        $request = new Request();
        $request->setMethod('put');
        $request->setContent(json_encode($inputFilter));
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');

        $module     = 'InputFilter';
        $controller = 'InputFilter\V1\Rest\Foo\Controller';
        $params = array(
            'name' => $module,
            'controller_service_name' => $controller
        );
        $routeMatch = new RouteMatch($params);
        $routeMatch->setMatchedRouteName('zf-apigility-admin/api/module/rest-service/rest_input_filter');
        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);

        $parameters = new ParameterDataContainer();
        $parameters->setBodyParams($inputFilter);
        $event->setParam('ZFContentNegotiationParameterData', $parameters);

        $plugins = new PluginManager();
        $plugins->setInvokableClass('bodyParams', 'ZF\ContentNegotiation\ControllerPlugin\BodyParams');

        $this->controller->setRequest($request);
        $this->controller->setEvent($event);
        $this->controller->setPluginManager($plugins);

        $result     = $this->controller->indexAction();
        $this->assertInstanceOf('ZF\ContentNegotiation\ViewModel', $result);
        $payload = $result->payload;
        $this->assertInstanceOf('ZF\Hal\Entity', $payload);
        $entity = $payload->entity;
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\InputFilterEntity', $entity);

        $config    = include $this->basePath . '/module.config.php';
        $validator = $config['zf-content-validation'][$controller]['input_filter'];
        $expected  = $config['input_filter_specs'][$validator];
        $expected['input_filter_name'] = $validator;
        $this->assertEquals($expected, $entity->getArrayCopy());
    }

    public function testRemoveInputFilter()
    {
        $request = new Request();
        $request->setMethod('delete');
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');

        $module     = 'InputFilter';
        $controller = 'InputFilter\V1\Rest\Foo\Controller';
        $validator  = 'InputFilter\V1\Rest\Foo\Validator';
        $params = array(
            'name' => $module,
            'controller_service_name' => $controller,
            'input_filter_name' => $validator,
        );
        $routeMatch = new RouteMatch($params);
        $routeMatch->setMatchedRouteName('zf-apigility-admin/api/module/rest-service/rest_input_filter');
        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);

        $this->controller->setRequest($request);
        $this->controller->setEvent($event);

        $result = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\Http\Response', $result);
        $this->assertEquals(204, $result->getStatusCode());
    }
}
