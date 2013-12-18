<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
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
        $collection = $payload->collection;
        $this->assertInternalType('array', $collection);
        $inputFilter = array_shift($collection);
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\InputFilterEntity', $inputFilter);

        $inputFilterKey = $this->config['zf-content-validation'][$controller]['input_filter'];
        $expected = $this->config['input_filters'][$inputFilterKey];
        $expected['name'] = $inputFilterKey;
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
        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);

        $this->controller->setRequest($request);
        $this->controller->setEvent($event);

        $result = $this->controller->indexAction();
        $this->assertInstanceOf('ZF\ContentNegotiation\ViewModel', $result);
        $payload = $result->payload;
        $this->assertInstanceOf('ZF\Hal\Resource', $payload);
        $resource = $payload->resource;
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\InputFilterEntity', $resource);

        $expected = $this->config['input_filters'][$validator];
        $expected['name'] = $validator;
        $this->assertEquals($expected, $resource->getArrayCopy());
    }

    public function testAddInputFilter()
    {
        $inputfilter = [
            [
                'name' => 'bar',
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'type' => 127,
                        ],
                    ],
                    [
                        'name' => 'Digits',
                    ],
                ],
            ],
        ];

        $request = new Request();
        $request->setMethod('put');
        $request->setContent(json_encode($inputfilter));
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');

        $module     = 'InputFilter';
        $controller = 'InputFilter\V1\Rest\Foo\Controller';
        $params = array(
            'name' => $module,
            'controller_service_name' => $controller
        );
        $routeMatch = new RouteMatch($params);
        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);

        $parameters = new ParameterDataContainer();
        $parameters->setBodyParams($inputfilter);
        $event->setParam('ZFContentNegotiationParameterData', $parameters);

        $plugins = new PluginManager();
        $plugins->setInvokableClass('bodyParams', 'ZF\ContentNegotiation\ControllerPlugin\BodyParams');

        $this->controller->setRequest($request);
        $this->controller->setEvent($event);
        $this->controller->setPluginManager($plugins);

        $result     = $this->controller->indexAction();
        $this->assertInstanceOf('ZF\ContentNegotiation\ViewModel', $result);
        $payload = $result->payload;
        $this->assertInstanceOf('ZF\Hal\Resource', $payload);
        $resource = $payload->resource;
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\InputFilterEntity', $resource);

        $config    = include $this->basePath . '/module.config.php';
        $validator = $config['zf-content-validation'][$controller]['input_filter'];
        $expected  = $config['input_filters'][$validator];
        $expected['name'] = $validator;
        $this->assertEquals($expected, $resource->getArrayCopy());
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
        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);

        $this->controller->setRequest($request);
        $this->controller->setEvent($event);

        $result = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\Http\Response', $result);
        $this->assertEquals(204, $result->getStatusCode());
    }
}
