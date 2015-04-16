<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use BarConf;
use BazConf;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;
use Zend\Config\Writer\PhpArray;
use ZF\Apigility\Admin\Model\ModuleEntity;
use ZF\Apigility\Admin\Model\ModulePathSpec;
use ZF\Apigility\Admin\Model\NewRestServiceEntity;
use ZF\Apigility\Admin\Model\RestServiceEntity;
use ZF\Apigility\Admin\Model\RestServiceModel;
use ZF\Apigility\Admin\Model\VersioningModel;
use ZF\Configuration\ResourceFactory;
use ZF\Configuration\ModuleUtils;

class RestServiceModelTest extends TestCase
{
    /**
     * Remove a directory even if not empty (recursive delete)
     *
     * @param  string $dir
     * @return boolean
     */
    protected function removeDir($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = "$dir/$file";
            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                unlink($path);
            }
        }
        return rmdir($dir);
    }
    protected function cleanUpAssets()
    {
        $pathSpec = (empty($this->modules)) ? 'psr-0' : $this->modules->getPathSpec();

        $modulePath = array(
            'psr-0' => '%s/src/%s/V*',
            'psr-4' => '%s/src/V*'
        );

        $basePath   = sprintf('%s/TestAsset/module/%s', __DIR__, $this->module);
        $configPath = $basePath . '/config';
        foreach (glob(sprintf($modulePath[$pathSpec], $basePath, $this->module)) as $dir) {
            $this->removeDir($dir);
        }
        copy($configPath . '/module.config.php.dist', $configPath . '/module.config.php');
    }

//    protected function cleanUpAssets()
//    {
//        $basePath   = sprintf('%s/TestAsset/module/%s', __DIR__, $this->module);
//        $configPath = $basePath . '/config';
//        foreach (glob(sprintf('%s/src/%s/V*', $basePath, $this->module)) as $dir) {
//            $this->removeDir($dir);
//        }
//        copy($configPath . '/module.config.php.dist', $configPath . '/module.config.php');
//    }

    public function setUp()
    {
        $this->module = 'BarConf';
        $this->cleanUpAssets();

        $modules = array(
            'BarConf' => new BarConf\Module(),
            'BazConf' => new BazConf\Module()
        );

        $this->moduleEntity  = new ModuleEntity($this->module, array(), array(), false);
        $this->moduleManager = $this->getMockBuilder('Zend\ModuleManager\ModuleManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $this->writer   = new PhpArray();
        $moduleUtils    = new ModuleUtils($this->moduleManager);
        $this->modules  = new ModulePathSpec($moduleUtils);
        $this->resource = new ResourceFactory($moduleUtils, $this->writer);
        $this->codeRest = new RestServiceModel(
            $this->moduleEntity,
            $this->modules,
            $this->resource->factory('BarConf')
        );
    }

    public function tearDown()
    {
        $this->cleanUpAssets();
    }

    public function getCreationPayload()
    {
        $payload = new NewRestServiceEntity();
        $payload->exchangeArray(array(
            'service_name'               => 'foo',
            'route_match'                => '/api/foo',
            'route_identifier_name'      => 'foo_id',
            'collection_name'            => 'foo',
            'entity_http_methods'        => array('GET', 'PATCH'),
            'collection_http_methods'    => array('GET', 'POST'),
            'collection_query_whitelist' => array('sort', 'filter'),
            'page_size'                  => 10,
            'page_size_param'            => 'p',
            'selector'                   => 'HalJson',
            'accept_whitelist'           => array('application/json', 'application/*+json'),
            'content_type_whitelist'     => array('application/json'),
            'hydrator_name'              => 'Zend\Stdlib\Hydrator\ObjectProperty',
        ));

        return $payload;
    }

    public function testRejectInvalidRestServiceName1()
    {
        $this->setExpectedException('ZF\Rest\Exception\CreationException');
        $restServiceEntity = new NewRestServiceEntity();
        $restServiceEntity->exchangeArray(array('servicename' => 'Foo Bar'));
        $this->codeRest->createService($restServiceEntity);
    }

    public function testRejectInvalidRestServiceName2()
    {
        $this->setExpectedException('ZF\Rest\Exception\CreationException');
        $restServiceEntity = new NewRestServiceEntity();
        $restServiceEntity->exchangeArray(array('serivcename' => 'Foo:Bar'));
        $this->codeRest->createService($restServiceEntity);
    }

    public function testRejectInvalidRestServiceName3()
    {
        $this->setExpectedException('ZF\Rest\Exception\CreationException');
        $restServiceEntity = new NewRestServiceEntity();
        $restServiceEntity->exchangeArray(array('servicename' => 'Foo/Bar'));
        $this->codeRest->createService($restServiceEntity);
    }

    public function testCanCreateControllerServiceNameFromServiceNameSpace()
    {
        $this->assertEquals(
            'BarConf\V1\Rest\Foo\Bar\Baz\Controller',
            $this->codeRest->createControllerServiceName('Foo\Bar\Baz')
        );
    }

    public function testCanCreateControllerServiceNameFromServiceName()
    {
        $this->assertEquals('BarConf\V1\Rest\Foo\Controller', $this->codeRest->createControllerServiceName('Foo'));
    }

    public function testCreateResourceClassReturnsClassNameCreated()
    {
        $resourceClass = $this->codeRest->createResourceClass('Foo');
        $this->assertEquals('BarConf\V1\Rest\Foo\FooResource', $resourceClass);
    }

    public function testCreateResourceClassCreatesClassFileWithNamedResourceClass()
    {
        $resourceClass = $this->codeRest->createResourceClass('Foo');

        $className = str_replace($this->module . '\\V1\\Rest\\Foo\\', '', $resourceClass);
        $path      = realpath(__DIR__) . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Foo/' . $className . '.php';
        $this->assertTrue(file_exists($path));

        require_once $path;

        $r = new ReflectionClass($resourceClass);
        $this->assertInstanceOf('ReflectionClass', $r);
        $parent = $r->getParentClass();
        $this->assertEquals('ZF\Rest\AbstractResourceListener', $parent->getName());
    }

    /**
     * @group feature/psr4
     */
    public function testCreateResourceClassCreatesClassFileWithNamedResourceClassPSR4()
    {
        $this->module = 'BazConf';
        $this->moduleEntity  = new ModuleEntity($this->module);
        $moduleUtils    = new ModuleUtils($this->moduleManager);
        $this->modules  = new ModulePathSpec($moduleUtils, 'psr-4', __DIR__ . '/TestAsset');
        $this->codeRest = new RestServiceModel(
            $this->moduleEntity,
            $this->modules,
            $this->resource->factory('BazConf')
        );

        $resourceClass = $this->codeRest->createResourceClass('Foo');

        $className = str_replace($this->module . '\\V1\\Rest\\Foo\\', '', $resourceClass);
        $path      = realpath(__DIR__) . '/TestAsset/module/BazConf/src/V1/Rest/Foo/' . $className . '.php';
        $this->assertTrue(file_exists($path));

        require_once $path;

        $r = new ReflectionClass($resourceClass);
        $this->assertInstanceOf('ReflectionClass', $r);
        $parent = $r->getParentClass();
        $this->assertEquals('ZF\Rest\AbstractResourceListener', $parent->getName());
    }

    public function testCreateResourceClassAddsInvokableToConfiguration()
    {
        $resourceClass = $this->codeRest->createResourceClass('Foo');

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $this->assertArrayHasKey('service_manager', $config);
        $this->assertArrayHasKey('factories', $config['service_manager']);
        $this->assertArrayHasKey($resourceClass, $config['service_manager']['factories']);
        $this->assertEquals($resourceClass . 'Factory', $config['service_manager']['factories'][$resourceClass]);
    }

    public function testCreateResourceClassCreateFactory()
    {
        $resourceClass = $this->codeRest->createResourceClass('Foo');

        $className = str_replace($this->module . '\\V1\\Rest\\Foo\\', '', $resourceClass . 'Factory');
        $path      = realpath(__DIR__) . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Foo/' . $className . '.php';
        $this->assertTrue(file_exists($path));
    }

    public function testCreateEntityClassReturnsClassNameCreated()
    {
        $entityClass = $this->codeRest->createEntityClass('Foo');
        $this->assertEquals('BarConf\V1\Rest\Foo\FooEntity', $entityClass);
    }

    public function testCreateEntityClassCreatesClassFileWithNamedEntityClass()
    {
        $entityClass = $this->codeRest->createEntityClass('Foo');

        $className = str_replace($this->module . '\\V1\\Rest\\Foo\\', '', $entityClass);
        $path      = realpath(__DIR__) . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Foo/' . $className . '.php';
        $this->assertTrue(file_exists($path));

        require_once $path;

        $r = new ReflectionClass($entityClass);
        $this->assertInstanceOf('ReflectionClass', $r);
        $this->assertFalse($r->getParentClass());
    }

    public function testCreateCollectionClassReturnsClassNameCreated()
    {
        $collectionClass = $this->codeRest->createCollectionClass('Foo');
        $this->assertEquals('BarConf\V1\Rest\Foo\FooCollection', $collectionClass);
    }

    public function testCreateCollectionClassCreatesClassFileWithNamedCollectionClass()
    {
        $collectionClass = $this->codeRest->createCollectionClass('Foo');

        $className = str_replace($this->module . '\\V1\\Rest\\Foo\\', '', $collectionClass);
        $path      = realpath(__DIR__) . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Foo/' . $className . '.php';
        $this->assertTrue(file_exists($path));

        require_once $path;

        $r = new ReflectionClass($collectionClass);
        $this->assertInstanceOf('ReflectionClass', $r);
        $parent = $r->getParentClass();
        $this->assertEquals('Zend\Paginator\Paginator', $parent->getName());
    }

    public function testCreateRouteReturnsNewRouteName()
    {
        $routeName = $this->codeRest->createRoute('FooBar', '/foo-bar', 'foo_bar_id', 'BarConf\Rest\FooBar\Controller');
        $this->assertEquals('bar-conf.rest.foo-bar', $routeName);
    }

    public function testCreateRouteWritesRouteConfiguration()
    {
        $routeName = $this->codeRest->createRoute('FooBar', '/foo-bar', 'foo_bar_id', 'BarConf\Rest\FooBar\Controller');

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $this->assertArrayHasKey('router', $config);
        $this->assertArrayHasKey('routes', $config['router']);
        $routes = $config['router']['routes'];

        $this->assertArrayHasKey($routeName, $routes);
        $expected = array(
            'type' => 'Segment',
            'options' => array(
                'route' => '/foo-bar[/:foo_bar_id]',
                'defaults' => array(
                    'controller' => 'BarConf\Rest\FooBar\Controller',
                ),
            ),
        );
        $this->assertEquals($expected, $routes[$routeName]);
    }

    public function testCreateRouteWritesVersioningConfiguration()
    {
        $routeName = $this->codeRest->createRoute('FooBar', '/foo-bar', 'foo_bar_id', 'BarConf\Rest\FooBar\Controller');

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $this->assertArrayHasKey('router', $config);
        $this->assertArrayHasKey('routes', $config['router']);
        $routes = $config['zf-versioning']['uri'];

        $this->assertContains($routeName, $routes);
    }

    public function testCreateRestConfigWritesRestConfiguration()
    {
        $details = $this->getCreationPayload();
        $details->exchangeArray(array(
            'entity_class'     => 'BarConf\Rest\Foo\FooEntity',
            'collection_class' => 'BarConf\Rest\Foo\FooCollection',
        ));
        $this->codeRest->createRestConfig(
            $details,
            'BarConf\Rest\Foo\Controller',
            'BarConf\Rest\Foo\FooResource',
            'bar-conf.rest.foo'
        );
        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';

        $this->assertArrayHasKey('zf-rest', $config);
        $this->assertArrayHasKey('BarConf\Rest\Foo\Controller', $config['zf-rest']);
        $config = $config['zf-rest']['BarConf\Rest\Foo\Controller'];

        $expected = array(
            'service_name'               => 'foo',
            'listener'                   => 'BarConf\Rest\Foo\FooResource',
            'route_name'                 => 'bar-conf.rest.foo',
            'route_identifier_name'      => $details->routeIdentifierName,
            'collection_name'            => $details->collectionName,
            'entity_http_methods'        => $details->entityHttpMethods,
            'collection_http_methods'    => $details->collectionHttpMethods,
            'collection_query_whitelist' => $details->collectionQueryWhitelist,
            'page_size'                  => $details->pageSize,
            'page_size_param'            => $details->pageSizeParam,
            'entity_class'               => $details->entityClass,
            'collection_class'           => $details->collectionClass,
        );
        $this->assertEquals($expected, $config);
    }

    public function testCreateContentNegotiationConfigWritesContentNegotiationConfiguration()
    {
        $details = $this->getCreationPayload();
        $this->codeRest->createContentNegotiationConfig($details, 'BarConf\Rest\Foo\Controller');
        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';

        $this->assertArrayHasKey('zf-content-negotiation', $config);
        $config = $config['zf-content-negotiation'];

        $this->assertArrayHasKey('controllers', $config);
        $this->assertEquals(array(
            'BarConf\Rest\Foo\Controller' => $details->selector,
        ), $config['controllers']);

        $this->assertArrayHasKey('accept_whitelist', $config);
        $this->assertEquals(array(
            'BarConf\Rest\Foo\Controller' => $details->acceptWhitelist,
        ), $config['accept_whitelist'], var_export($config, 1));

        $this->assertArrayHasKey('content_type_whitelist', $config);
        $this->assertEquals(array(
            'BarConf\Rest\Foo\Controller' => $details->contentTypeWhitelist,
        ), $config['content_type_whitelist'], var_export($config, 1));
    }

    public function testCreateHalConfigWritesHalConfiguration()
    {
        $details = $this->getCreationPayload();
        $this->codeRest->createHalConfig(
            $details,
            'BarConf\Rest\Foo\FooEntity',
            'BarConf\Rest\Foo\FooCollection',
            'bar-conf.rest.foo'
        );
        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';

        $this->assertArrayHasKey('zf-hal', $config);
        $this->assertArrayHasKey('metadata_map', $config['zf-hal']);
        $config = $config['zf-hal']['metadata_map'];

        $this->assertArrayHasKey('BarConf\Rest\Foo\FooEntity', $config);
        $this->assertEquals(array(
            'route_identifier_name'  => $details->routeIdentifierName,
            'route_name'             => 'bar-conf.rest.foo',
            'hydrator'               => 'Zend\Stdlib\Hydrator\ObjectProperty',
            'entity_identifier_name' => 'id',
        ), $config['BarConf\Rest\Foo\FooEntity']);

        $this->assertArrayHasKey('BarConf\Rest\Foo\FooCollection', $config);
        $this->assertEquals(array(
            'route_identifier_name'  => $details->routeIdentifierName,
            'route_name'             => 'bar-conf.rest.foo',
            'is_collection'          => true,
            'entity_identifier_name' => 'id',
        ), $config['BarConf\Rest\Foo\FooCollection']);
    }

    public function testCreateServiceReturnsRestServiceEntityOnSuccess()
    {
        $details = $this->getCreationPayload();
        $result  = $this->codeRest->createService($details);
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\RestServiceEntity', $result);

        $this->assertEquals('BarConf', $result->module);
        $this->assertEquals('foo', $result->serviceName);
        $this->assertEquals('BarConf\V1\Rest\Foo\Controller', $result->controllerServiceName);
        $this->assertEquals('BarConf\V1\Rest\Foo\FooResource', $result->resourceClass);
        $this->assertEquals('BarConf\V1\Rest\Foo\FooEntity', $result->entityClass);
        $this->assertEquals('BarConf\V1\Rest\Foo\FooCollection', $result->collectionClass);
        $this->assertEquals('bar-conf.rest.foo', $result->routeName);
        $this->assertEquals(
            array('application/vnd.bar-conf.v1+json', 'application/hal+json', 'application/json'),
            $result->acceptWhitelist
        );
        $this->assertEquals(
            array('application/vnd.bar-conf.v1+json', 'application/json'),
            $result->contentTypeWhitelist
        );
    }

    public function testCreateServiceUsesDefaultContentNegotiation()
    {
        $payload = new NewRestServiceEntity();
        $payload->exchangeArray(array(
            'service_name' => 'foo',
        ));
        $result  = $this->codeRest->createService($payload);
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\RestServiceEntity', $result);
        $this->assertEquals(
            array('application/vnd.bar-conf.v1+json', 'application/hal+json', 'application/json'),
            $result->acceptWhitelist
        );
        $this->assertEquals(
            array('application/vnd.bar-conf.v1+json', 'application/json'),
            $result->contentTypeWhitelist
        );
    }

    public function testCanFetchServiceAfterCreation()
    {
        $details = $this->getCreationPayload();
        $result  = $this->codeRest->createService($details);

        $service = $this->codeRest->fetch('BarConf\V1\Rest\Foo\Controller');
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\RestServiceEntity', $service);

        $this->assertEquals('BarConf', $service->module);
        $this->assertEquals('foo', $service->serviceName);
        $this->assertEquals('BarConf\V1\Rest\Foo\Controller', $service->controllerServiceName);
        $this->assertEquals('BarConf\V1\Rest\Foo\FooResource', $service->resourceClass);
        $this->assertEquals('BarConf\V1\Rest\Foo\FooEntity', $service->entityClass);
        $this->assertEquals('BarConf\V1\Rest\Foo\FooCollection', $service->collectionClass);
        $this->assertEquals('bar-conf.rest.foo', $service->routeName);
        $this->assertEquals('/api/foo[/:foo_id]', $service->routeMatch);
        $this->assertEquals('Zend\Stdlib\Hydrator\ObjectProperty', $service->hydratorName);
    }

    public function testFetchServiceUsesEntityAndCollectionClassesDiscoveredInRestConfiguration()
    {
        $details = $this->getCreationPayload();
        $details->exchangeArray(array(
            'entity_class'     => 'ZFTest\Apigility\Admin\Model\TestAsset\Entity',
            'collection_class' => 'ZFTest\Apigility\Admin\Model\TestAsset\Collection',
        ));
        $result  = $this->codeRest->createService($details);

        $service = $this->codeRest->fetch('BarConf\V1\Rest\Foo\Controller');
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\RestServiceEntity', $service);

        $this->assertEquals('ZFTest\Apigility\Admin\Model\TestAsset\Entity', $service->entityClass);
        $this->assertEquals('ZFTest\Apigility\Admin\Model\TestAsset\Collection', $service->collectionClass);
    }

    public function testCanUpdateRouteForExistingService()
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $patch = new RestServiceEntity();
        $patch->exchangeArray(array(
            'controller_service_name' => 'BarConf\Rest\Foo\Controller',
            'route_match'             => '/api/bar/foo',
        ));

        $this->codeRest->updateRoute($original, $patch);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $this->assertArrayHasKey('router', $config);
        $this->assertArrayHasKey('routes', $config['router']);
        $this->assertArrayHasKey($original->routeName, $config['router']['routes']);
        $routeConfig = $config['router']['routes'][$original->routeName];
        $this->assertArrayHasKey('options', $routeConfig);
        $this->assertArrayHasKey('route', $routeConfig['options']);
        $this->assertEquals('/api/bar/foo', $routeConfig['options']['route']);
    }

    public function testCanUpdateRestConfigForExistingService()
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $options = array(
            'page_size'                  => 30,
            'page_size_param'            => 'r',
            'collection_query_whitelist' => array('f', 's'),
            'collection_http_methods'    => array('GET'),
            'entity_http_methods'        => array('GET'),
            'entity_class'               => 'ZFTest\Apigility\Admin\Model\TestAsset\Entity',
            'collection_class'           => 'ZFTest\Apigility\Admin\Model\TestAsset\Collection',
        );
        $patch = new RestServiceEntity();
        $patch->exchangeArray($options);

        $this->codeRest->updateRestConfig($original, $patch);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $this->assertArrayHasKey('zf-rest', $config);
        $this->assertArrayHasKey($original->controllerServiceName, $config['zf-rest']);
        $test = $config['zf-rest'][$original->controllerServiceName];

        foreach ($options as $key => $value) {
            $this->assertEquals($value, $test[$key]);
        }
    }

    public function testCanUpdateContentNegotiationConfigForExistingService()
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $options = array(
            'selector'               => 'Json',
            'accept_whitelist'       => array('application/json'),
            'content_type_whitelist' => array('application/json'),
        );
        $patch = new RestServiceEntity();
        $patch->exchangeArray($options);

        $this->codeRest->updateContentNegotiationConfig($original, $patch);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $this->assertArrayHasKey('zf-content-negotiation', $config);
        $config = $config['zf-content-negotiation'];

        $this->assertArrayHasKey('controllers', $config);
        $this->assertArrayHasKey($original->controllerServiceName, $config['controllers']);
        $this->assertEquals($options['selector'], $config['controllers'][$original->controllerServiceName]);

        $this->assertArrayHasKey('accept_whitelist', $config);
        $this->assertArrayHasKey($original->controllerServiceName, $config['accept_whitelist']);
        $this->assertEquals(
            $options['accept_whitelist'],
            $config['accept_whitelist'][$original->controllerServiceName]
        );

        $this->assertArrayHasKey('content_type_whitelist', $config);
        $this->assertArrayHasKey($original->controllerServiceName, $config['content_type_whitelist']);
        $this->assertEquals(
            $options['content_type_whitelist'],
            $config['content_type_whitelist'][$original->controllerServiceName]
        );
    }

    public function testCanUpdateHalConfigForExistingService()
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $options = array(
            'hydrator_name'         => 'Zend\Stdlib\Hydrator\Reflection',
            'route_identifier_name' => 'custom_foo_id',
            'route_name'            => 'my/custom/route',
        );
        $patch = new RestServiceEntity();
        $patch->exchangeArray($options);

        $this->codeRest->updateHalConfig($original, $patch);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $this->assertArrayHasKey('zf-hal', $config);
        $this->assertArrayHasKey('metadata_map', $config['zf-hal']);
        $config = $config['zf-hal']['metadata_map'];

        $entityName     = $original->entityClass;
        $collectionName = $original->collectionClass;
        $this->assertArrayHasKey($entityName, $config);
        $this->assertArrayHasKey($collectionName, $config);

        $entityConfig     = $config[$entityName];
        $collectionConfig = $config[$collectionName];

        $this->assertArrayHasKey('route_identifier_name', $entityConfig);
        $this->assertEquals($options['route_identifier_name'], $entityConfig['route_identifier_name']);
        $this->assertArrayHasKey('route_identifier_name', $collectionConfig);
        $this->assertEquals($options['route_identifier_name'], $collectionConfig['route_identifier_name']);

        $this->assertArrayHasKey('route_name', $entityConfig);
        $this->assertEquals($options['route_name'], $entityConfig['route_name']);
        $this->assertArrayHasKey('route_name', $collectionConfig);
        $this->assertEquals($options['route_name'], $collectionConfig['route_name']);

        $this->assertArrayHasKey('hydrator', $entityConfig);
        $this->assertEquals($options['hydrator_name'], $entityConfig['hydrator']);
        $this->assertArrayNotHasKey('hydrator', $collectionConfig);
    }

    public function testCanUpdateHalConfigForExistingServiceAndProvideNewEntityAndCollectionClasses()
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $options = array(
            'entity_class'          => 'ZFTest\Apigility\Admin\Model\TestAsset\Entity',
            'collection_class'      => 'ZFTest\Apigility\Admin\Model\TestAsset\Collection',
            'hydrator_name'         => 'Zend\Stdlib\Hydrator\Reflection',
            'route_identifier_name' => 'custom_foo_id',
            'route_name'            => 'my/custom/route',
        );
        $patch = new RestServiceEntity();
        $patch->exchangeArray($options);

        $this->codeRest->updateHalConfig($original, $patch);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $this->assertArrayHasKey('zf-hal', $config);
        $this->assertArrayHasKey('metadata_map', $config['zf-hal']);
        $config = $config['zf-hal']['metadata_map'];

        $entityName     = $patch->entityClass;
        $collectionName = $patch->collectionClass;

        $this->assertArrayHasKey($entityName, $config);
        $this->assertArrayHasKey($collectionName, $config);

        $entityConfig     = $config[$entityName];
        $collectionConfig = $config[$collectionName];

        $this->assertArrayHasKey('route_identifier_name', $entityConfig);
        $this->assertEquals($options['route_identifier_name'], $entityConfig['route_identifier_name']);
        $this->assertArrayHasKey('route_identifier_name', $collectionConfig);
        $this->assertEquals($options['route_identifier_name'], $collectionConfig['route_identifier_name']);

        $this->assertArrayHasKey('route_name', $entityConfig);
        $this->assertEquals($options['route_name'], $entityConfig['route_name']);
        $this->assertArrayHasKey('route_name', $collectionConfig);
        $this->assertEquals($options['route_name'], $collectionConfig['route_name']);

        $this->assertArrayHasKey('hydrator', $entityConfig);
        $this->assertEquals($options['hydrator_name'], $entityConfig['hydrator']);
        $this->assertArrayNotHasKey('hydrator', $collectionConfig);
    }

    public function testUpdateServiceReturnsUpdatedRepresentation()
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $updates = array(
            'route_match'                => '/api/bar/foo',
            'page_size'                  => 30,
            'page_size_param'            => 'r',
            'collection_query_whitelist' => array('f', 's'),
            'collection_http_methods'    => array('GET'),
            'entity_http_methods'        => array('GET'),
            'selector'                   => 'Json',
            'accept_whitelist'           => array('application/json'),
            'content_type_whitelist'     => array('application/json'),
        );
        $patch = new RestServiceEntity();
        $patch->exchangeArray(array_merge(array(
            'controller_service_name'    => 'BarConf\V1\Rest\Foo\Controller',
        ), $updates));

        $updated = $this->codeRest->updateService($patch);
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\RestServiceEntity', $updated);

        $values = $updated->getArrayCopy();

        foreach ($updates as $key => $value) {
            $this->assertArrayHasKey($key, $values);
            if ($key === 'route_match') {
                $this->assertEquals(0, strpos($value, $values[$key]));
                continue;
            }
            $this->assertEquals($value, $values[$key]);
        }
    }

    public function testFetchListenersCanReturnAlternateEntities()
    {
        $details = $this->getCreationPayload();
        $this->codeRest->createService($details);

        $alternateEntity = new RestServiceEntity();
        $this->codeRest->getEventManager()->attach('fetch', function ($e) use ($alternateEntity) {
            return $alternateEntity;
        });

        $result = $this->codeRest->fetch('BarConf\V1\Rest\Foo\Controller');
        $this->assertSame($alternateEntity, $result);
    }

    public function testCanDeleteAService()
    {
        $details = $this->getCreationPayload();
        $service = $this->codeRest->createService($details);

        $this->assertTrue($this->codeRest->deleteService($service->controllerServiceName));

        $fooPath = __DIR__ . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Foo';
        $this->assertTrue(file_exists($fooPath));

        $this->setExpectedException('ZF\Apigility\Admin\Exception\RuntimeException', 'find', 404);
        $this->codeRest->fetch($service->controllerServiceName);
    }

    /**
     * @group feature/psr4
     */
    public function testCanDeleteAServicePSR4()
    {
        $this->module = 'BazConf';
        $this->moduleEntity  = new ModuleEntity($this->module);
        $moduleUtils    = new ModuleUtils($this->moduleManager);
        $this->modules  = new ModulePathSpec($moduleUtils, 'psr-4', __DIR__ . '/TestAsset');
        $this->codeRest = new RestServiceModel(
            $this->moduleEntity,
            $this->modules,
            $this->resource->factory('BazConf')
        );

        $details = $this->getCreationPayload();
        $service = $this->codeRest->createService($details);

        $this->assertTrue($this->codeRest->deleteService($service->controllerServiceName));

        $fooPath = __DIR__ . '/TestAsset/module/BazConf/src/V1/Rest/Foo';
        $this->assertTrue(file_exists($fooPath));

        $this->setExpectedException('ZF\Apigility\Admin\Exception\RuntimeException', 'find', 404);
        $this->codeRest->fetch($service->controllerServiceName);
    }

    public function testCanDeleteAServiceRecursive()
    {
        $details = $this->getCreationPayload();
        $service = $this->codeRest->createService($details);

        $this->assertTrue($this->codeRest->deleteService($service->controllerServiceName, true));

        $fooPath = __DIR__ . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Foo';
        $this->assertFalse(file_exists($fooPath));
    }

    /**
     * @group feature/psr4
     */
    public function testCanDeleteAServiceRecursivePSR4()
    {
        $this->module = 'BazConf';
        $this->moduleEntity  = new ModuleEntity($this->module);
        $moduleUtils    = new ModuleUtils($this->moduleManager);
        $this->modules  = new ModulePathSpec($moduleUtils, 'psr-4', __DIR__ . '/TestAsset');
        $this->codeRest = new RestServiceModel(
            $this->moduleEntity,
            $this->modules,
            $this->resource->factory('BazConf')
        );

        $details = $this->getCreationPayload();
        $service = $this->codeRest->createService($details);

        $this->assertTrue($this->codeRest->deleteService($service->controllerServiceName, true));

        $fooPath = __DIR__ . '/TestAsset/module/BazConf/src/V1/Rest/Foo';
        $this->assertFalse(file_exists($fooPath));
    }

    /**
     * @depends testCanDeleteAService
     */
    public function testDeletingAServiceRemovesAllRelatedConfigKeys()
    {
        $details = $this->getCreationPayload();
        $service = $this->codeRest->createService($details);

        $this->assertTrue($this->codeRest->deleteService($service->controllerServiceName));
        $path = __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $config = include $path;
        $this->assertInternalType('array', $config);
        $this->assertInternalType('array', $config['zf-rest']);
        $this->assertInternalType('array', $config['zf-versioning']);
        $this->assertInternalType('array', $config['router']['routes']);
        $this->assertInternalType('array', $config['zf-content-negotiation']);
        $this->assertInternalType('array', $config['service_manager']);
        $this->assertInternalType('array', $config['zf-hal']);

        $this->assertArrayNotHasKey('BarConf\V1\Rest\Foo\Controller', $config['zf-rest'], 'REST entry not deleted');
        $this->assertArrayNotHasKey('bar-conf.rest.foo', $config['router']['routes'], 'Route not deleted');
        $this->assertNotContains('bar-conf.rest.foo', $config['zf-versioning']['uri'], 'Versioning not deleted');
        // @codingStandardsIgnoreStart
        $this->assertArrayNotHasKey('BarConf\\V1\\Rest\\Foo\\Controller', $config['zf-content-negotiation']['controllers'], 'Content Negotiation controllers entry not deleted');
        $this->assertArrayNotHasKey('BarConf\V1\Rest\Foo\Controller', $config['zf-content-negotiation']['accept_whitelist'], 'Content Negotiation accept whitelist entry not deleted');
        $this->assertArrayNotHasKey('BarConf\V1\Rest\Foo\Controller', $config['zf-content-negotiation']['content_type_whitelist'], 'Content Negotiation content-type whitelist entry not deleted');
        // @codingStandardsIgnoreEnd
        foreach ($config['service_manager'] as $serviceType => $services) {
            $this->assertArrayNotHasKey('BarConf\V1\Rest\Foo\FooResource', $services, 'Service entry not deleted');
        }
        $this->assertArrayNotHasKey(
            'BarConf\V1\Rest\Foo\FooEntity',
            $config['zf-hal']['metadata_map'],
            'HAL entity not deleted'
        );
        $this->assertArrayNotHasKey(
            'BarConf\V1\Rest\Foo\FooCollection',
            $config['zf-hal']['metadata_map'],
            'HAL collection not deleted'
        );
    }

    /**
     * @depends testDeletingAServiceRemovesAllRelatedConfigKeys
     */
    public function testDeletingNewerVersionOfServiceDoesNotRemoveRouteOrVersioningConfiguration()
    {
        $details = $this->getCreationPayload();
        $service = $this->codeRest->createService($details);

        $path = __DIR__ . '/TestAsset/module/BarConf';
        $versioningModel = new VersioningModel($this->resource->factory('BarConf'));
        $this->assertTrue($versioningModel->createVersion('BarConf', 2));

        $serviceName = str_replace('1', '2', $service->controllerServiceName);
        $service = $this->codeRest->fetch($serviceName);
        $this->assertTrue($this->codeRest->deleteService($serviceName));

        $config = include $path . '/config/module.config.php';
        $this->assertInternalType('array', $config);
        $this->assertInternalType('array', $config['zf-versioning']);
        $this->assertInternalType('array', $config['router']['routes']);

        $this->assertArrayHasKey('BarConf\V1\Rest\Foo\Controller', $config['zf-rest']);
        $this->assertArrayNotHasKey('BarConf\V2\Rest\Foo\Controller', $config['zf-rest']);
        $this->assertArrayHasKey('bar-conf.rest.foo', $config['router']['routes'], 'Route DELETED');
        $this->assertContains('bar-conf.rest.foo', $config['zf-versioning']['uri'], 'Versioning DELETED');
    }

    /**
     * @group skeleton-37
     */
    public function testUpdateHalConfigShouldNotRemoveIsCollectionKey()
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $options = array(
            'hydrator_name'         => 'Zend\Stdlib\Hydrator\Reflection',
            'route_identifier_name' => 'custom_foo_id',
            'route_name'            => 'my/custom/route',
        );
        $patch = new RestServiceEntity();
        $patch->exchangeArray($options);

        $this->codeRest->updateHalConfig($original, $patch);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $this->assertArrayHasKey('zf-hal', $config);
        $this->assertArrayHasKey('metadata_map', $config['zf-hal']);
        $config = $config['zf-hal']['metadata_map'];

        $collectionName = $original->collectionClass;
        $this->assertArrayHasKey($collectionName, $config);

        $collectionConfig = $config[$collectionName];
        $this->assertArrayHasKey('is_collection', $collectionConfig);
        $this->assertTrue($collectionConfig['is_collection']);
    }

    /**
     * @group 76
     */
    public function testUpdateHalConfigShouldKeepExistingKeysIntact()
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $options = array(
            'hydrator_name'          => 'Zend\Stdlib\Hydrator\Reflection',
            'entity_identifier_name' => 'custom_foo_id',
        );
        $patch = new RestServiceEntity();
        $patch->exchangeArray($options);

        $this->codeRest->updateHalConfig($original, $patch);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $this->assertArrayHasKey('zf-hal', $config);
        $this->assertArrayHasKey('metadata_map', $config['zf-hal']);
        $config = $config['zf-hal']['metadata_map'];

        $entityName     = $original->entityClass;
        $collectionName = $original->collectionClass;
        $this->assertArrayHasKey($entityName, $config);
        $this->assertArrayHasKey($collectionName, $config);

        $entityConfig = $config[$entityName];
        $this->assertArrayHasKey('entity_identifier_name', $entityConfig);
        $this->assertArrayHasKey('route_identifier_name', $entityConfig);
        $this->assertArrayHasKey('route_name', $entityConfig);
        $this->assertEquals($options['entity_identifier_name'], $entityConfig['entity_identifier_name']);
        $this->assertEquals($original->routeIdentifierName, $entityConfig['route_identifier_name']);
        $this->assertEquals($original->routeName, $entityConfig['route_name']);

        $collectionConfig = $config[$collectionName];
        $this->assertArrayHasKey('entity_identifier_name', $entityConfig);
        $this->assertArrayHasKey('route_identifier_name', $entityConfig);
        $this->assertArrayHasKey('route_name', $entityConfig);
        $this->assertEquals($options['entity_identifier_name'], $entityConfig['entity_identifier_name']);
        $this->assertEquals($original->routeIdentifierName, $entityConfig['route_identifier_name']);
        $this->assertEquals($original->routeName, $entityConfig['route_name']);
    }

    /**
     * @group 72
     */
    public function testCanRemoveAllHttpVerbsWhenUpdating()
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $options = array(
            'collection_http_methods'    => array(),
            'entity_http_methods'        => array(),
        );
        $patch = new RestServiceEntity();
        $patch->exchangeArray($options);

        $this->codeRest->updateRestConfig($original, $patch);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $this->assertArrayHasKey('zf-rest', $config);
        $this->assertArrayHasKey($original->controllerServiceName, $config['zf-rest']);
        $test = $config['zf-rest'][$original->controllerServiceName];

        $this->assertEquals(array(), $test['collection_http_methods']);
        $this->assertEquals(array(), $test['entity_http_methods']);
    }

    /**
     * @group 170
     */
    public function testUpdateRestWillUpdateCollectionName()
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $options = array(
            'collection_name' => 'foo_bars',
        );
        $patch = new RestServiceEntity();
        $patch->exchangeArray($options);

        $this->codeRest->updateRestConfig($original, $patch);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $this->assertArrayHasKey('zf-rest', $config);
        $this->assertArrayHasKey($original->controllerServiceName, $config['zf-rest']);
        $test = $config['zf-rest'][$original->controllerServiceName];

        foreach ($options as $key => $value) {
            $this->assertEquals($value, $test[$key]);
        }
    }
}
