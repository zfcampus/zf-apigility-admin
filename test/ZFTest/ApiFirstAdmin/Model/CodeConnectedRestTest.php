<?php

namespace ZFTest\ApiFirstAdmin\Model;

use BarConf;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;
use Zend\Config\Writer\PhpArray;
use ZF\ApiFirstAdmin\Model\CodeConnectedRest;
use ZF\ApiFirstAdmin\Model\RestCreationEndpoint;
use ZF\ApiFirstAdmin\Model\RestEndpointMetadata;
use ZF\Configuration\ResourceFactory;
use ZF\Configuration\ModuleUtils;

require_once __DIR__ . '/TestAsset/module/BarConf/Module.php';

class CodeConnectedRestTest extends TestCase
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
        $basePath   = sprintf('%s/TestAsset/module/%s', __DIR__, $this->module);
        $configPath = $basePath . '/config';
        $srcPath    = $basePath . '/src';
        if (is_dir($srcPath)) {
            $this->removeDir($srcPath);
        }
        copy($configPath . '/module.config.php.dist', $configPath . '/module.config.php');
    }

    public function setUp()
    {
        $this->module = 'BarConf';
        $this->cleanUpAssets();

        $modules = array(
            'BarConf' => new BarConf\Module()
        );

        $this->moduleManager = $this->getMockBuilder('Zend\ModuleManager\ModuleManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $this->writer   = new PhpArray();
        $this->modules  = new ModuleUtils($this->moduleManager);
        $this->resource = new ResourceFactory($this->modules, $this->writer);
        $this->codeRest = new CodeConnectedRest($this->module, $this->modules, $this->resource->factory('BarConf'));
    }

    public function tearDown()
    {
        $this->cleanUpAssets();
    }

    public function getCreationPayload()
    {
        $payload = new RestCreationEndpoint();
        $payload->exchangeArray(array(
            'resource_name'              => 'foo',
            'route'                      => '/api/foo',
            'identifier_name'            => 'foo_id',
            'collection_name'            => 'foo',
            'resource_http_options'      => array('GET', 'PATCH'),
            'collection_http_options'    => array('GET', 'POST'),
            'collection_query_whitelist' => array('sort', 'filter'),
            'page_size'                  => 10,
            'page_size_param'            => 'p',
            'selector'                   => 'HalJson',
            'accept_whitelist'           => array('application/json', 'application/*+json'),
            'content_type_whitelist'     => array('application/json'),
        ));
        return $payload;
    }

    public function testCanCreateControllerServiceNameFromResourceName()
    {
        $this->assertEquals('BarConf\Controller\Foo', $this->codeRest->createControllerServiceName('Foo'));
    }

    public function testCreateResourceClassReturnsClassNameCreated()
    {
        $resourceClass = $this->codeRest->createResourceClass('Foo');
        $this->assertEquals('BarConf\FooResource', $resourceClass);
    }

    public function testCreateResourceClassCreatesClassFileWithNamedResourceClass()
    {
        $resourceClass = $this->codeRest->createResourceClass('Foo');

        $className = str_replace($this->module . '\\', '', $resourceClass);
        $path      = realpath(__DIR__) . '/TestAsset/module/BarConf/src/BarConf/' . $className . '.php';
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
        $this->assertArrayHasKey('invokables', $config['service_manager']);
        $this->assertArrayHasKey($resourceClass, $config['service_manager']['invokables']);
        $this->assertEquals($resourceClass, $config['service_manager']['invokables'][$resourceClass]);
    }

    public function testCreateEntityClassReturnsClassNameCreated()
    {
        $entityClass = $this->codeRest->createEntityClass('Foo');
        $this->assertEquals('BarConf\Foo', $entityClass);
    }

    public function testCreateEntityClassCreatesClassFileWithNamedEntityClass()
    {
        $entityClass = $this->codeRest->createEntityClass('Foo');

        $className = str_replace($this->module . '\\', '', $entityClass);
        $path      = realpath(__DIR__) . '/TestAsset/module/BarConf/src/BarConf/' . $className . '.php';
        $this->assertTrue(file_exists($path));

        require_once $path;

        $r = new ReflectionClass($entityClass);
        $this->assertInstanceOf('ReflectionClass', $r);
        $this->assertFalse($r->getParentClass());
    }

    public function testCreateCollectionClassReturnsClassNameCreated()
    {
        $collectionClass = $this->codeRest->createCollectionClass('Foo');
        $this->assertEquals('BarConf\FooCollection', $collectionClass);
    }

    public function testCreateCollectionClassCreatesClassFileWithNamedCollectionClass()
    {
        $collectionClass = $this->codeRest->createCollectionClass('Foo');

        $className = str_replace($this->module . '\\', '', $collectionClass);
        $path      = realpath(__DIR__) . '/TestAsset/module/BarConf/src/BarConf/' . $className . '.php';
        $this->assertTrue(file_exists($path));

        require_once $path;

        $r = new ReflectionClass($collectionClass);
        $this->assertInstanceOf('ReflectionClass', $r);
        $parent = $r->getParentClass();
        $this->assertEquals('Zend\Paginator\Paginator', $parent->getName());
    }

    public function testCreateRouteReturnsNewRouteName()
    {
        $routeName = $this->codeRest->createRoute('FooBar', '/foo-bar', 'foo_bar_id', 'BarConf\Controller\FooBar');
        $this->assertEquals('bar-conf.foo-bar', $routeName);
    }

    public function testCreateRouteWritesRouteConfiguration()
    {
        $routeName = $this->codeRest->createRoute('FooBar', '/foo-bar', 'foo_bar_id', 'BarConf\Controller\FooBar');

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
                    'controller' => 'BarConf\Controller\FooBar',
                ),
            ),
        );
        $this->assertEquals($expected, $routes[$routeName]);
    }

    public function testCreateRestConfigWritesRestConfiguration()
    {
        $details = $this->getCreationPayload();
        $this->codeRest->createRestConfig($details, 'BarConf\Controller\Foo', 'BarConf\FooResource', 'bar-conf.foo');
        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';

        $this->assertArrayHasKey('zf-rest', $config);
        $this->assertArrayHasKey('BarConf\Controller\Foo', $config['zf-rest']);
        $config = $config['zf-rest']['BarConf\Controller\Foo'];

        $expected = array(
            'listener'                   => 'BarConf\FooResource',
            'route_name'                 => 'bar-conf.foo',
            'identifier_name'            => $details->identifierName,
            'collection_name'            => $details->collectionName,
            'resource_http_options'      => $details->resourceHttpOptions,
            'collection_http_options'    => $details->collectionHttpOptions,
            'collection_query_whitelist' => $details->collectionQueryWhitelist,
            'page_size'                  => $details->pageSize,
            'page_size_param'            => $details->pageSizeParam,
        );
        $this->assertEquals($expected, $config);
    }

    public function testCreateContentNegotiationConfigWritesContentNegotiationConfiguration()
    {
        $details = $this->getCreationPayload();
        $this->codeRest->createContentNegotiationConfig($details, 'BarConf\Controller\Foo');
        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';

        $this->assertArrayHasKey('zf-content-negotiation', $config);
        $config = $config['zf-content-negotiation'];

        $this->assertArrayHasKey('controllers', $config);
        $this->assertEquals(array(
            'BarConf\Controller\Foo' => $details->selector,
        ), $config['controllers']);

        $this->assertArrayHasKey('accept-whitelist', $config);
        $this->assertEquals(array(
            'BarConf\Controller\Foo' => $details->acceptWhitelist,
        ), $config['accept-whitelist'], var_export($config, 1));

        $this->assertArrayHasKey('content-type-whitelist', $config);
        $this->assertEquals(array(
            'BarConf\Controller\Foo' => $details->contentTypeWhitelist,
        ), $config['content-type-whitelist'], var_export($config, 1));
    }

    public function testCreateHalConfigWritesHalConfiguration()
    {
        $details = $this->getCreationPayload();
        $this->codeRest->createHalConfig($details, 'BarConf\Foo', 'BarConf\FooCollection', 'bar-conf.foo');
        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';

        $this->assertArrayHasKey('zf-hal', $config);
        $this->assertArrayHasKey('metadata_map', $config['zf-hal']);
        $config = $config['zf-hal']['metadata_map'];

        $this->assertArrayHasKey('BarConf\Foo', $config);
        $this->assertEquals(array(
            'identifier_name' => $details->identifierName,
            'route_name'      => 'bar-conf.foo',
        ), $config['BarConf\Foo']);

        $this->assertArrayHasKey('BarConf\FooCollection', $config);
        $this->assertEquals(array(
            'identifier_name' => $details->identifierName,
            'route_name'      => 'bar-conf.foo',
            'is_collection'   => true,
        ), $config['BarConf\FooCollection']);
    }

    public function testCreateServiceReturnsRestEndpointMetadataOnSuccess()
    {
        $details = $this->getCreationPayload();
        $result  = $this->codeRest->createService($details);
        $this->assertInstanceOf('ZF\ApiFirstAdmin\Model\RestEndpointMetadata', $result);

        $this->assertEquals('BarConf', $result->module);
        $this->assertEquals('BarConf\Controller\Foo', $result->controllerServiceName);
        $this->assertEquals('BarConf\FooResource', $result->resourceClass);
        $this->assertEquals('BarConf\Foo', $result->entityClass);
        $this->assertEquals('BarConf\FooCollection', $result->collectionClass);
        $this->assertEquals('bar-conf.foo', $result->routeName);
    }

    public function testCanFetchEndpointAfterCreation()
    {
        $details = $this->getCreationPayload();
        $result  = $this->codeRest->createService($details);

        $endpoint = $this->codeRest->fetch('BarConf\Controller\Foo');
        $this->assertInstanceOf('ZF\ApiFirstAdmin\Model\RestEndpointMetadata', $endpoint);

        $this->assertEquals('BarConf', $endpoint->module);
        $this->assertEquals('BarConf\Controller\Foo', $endpoint->controllerServiceName);
        $this->assertEquals('BarConf\FooResource', $endpoint->resourceClass);
        $this->assertEquals('BarConf\Foo', $endpoint->entityClass);
        $this->assertEquals('BarConf\FooCollection', $endpoint->collectionClass);
        $this->assertEquals('bar-conf.foo', $endpoint->routeName);
        $this->assertEquals('/api/foo[/:foo_id]', $endpoint->route);
    }

    public function testCanUpdateRouteForExistingEndpoint()
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $patch = new RestEndpointMetadata();
        $patch->exchangeArray(array(
            'controller_service_name' => 'BarConf\Controller\Foo',
            'route'                   => '/api/bar/foo',
        ));

        $this->codeRest->updateRoute($original, $patch);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $this->assertArrayHasKey('router', $config);
        $this->assertArrayHasKey('routes', $config['router']);
        $this->assertArrayHasKey($original->routeName, $config['router']['routes']);
        $routeConfig = $config['router']['routes'][$original->routeName];
        $this->assertArrayHasKey('options', $routeConfig);
        $this->assertArrayHasKey('route', $routeConfig['options']);
        $this->assertEquals('/api/bar/foo[/:foo_id]', $routeConfig['options']['route']);
    }

    public function testCanUpdateRestConfigForExistingEndpoint()
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $options = array(
            'page_size'                  => 30,
            'page_size_param'            => 'r',
            'collection_query_whitelist' => array('f', 's'),
            'collection_http_options'    => array('GET'),
            'resource_http_options'      => array('GET'),
        );
        $patch = new RestEndpointMetadata();
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

    public function testCanUpdateContentNegotiationConfigForExistingEndpoint()
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $options = array(
            'selector'               => 'Json',
            'accept_whitelist'       => array('application/json'),
            'content_type_whitelist' => array('application/json'),
        );
        $patch = new RestEndpointMetadata();
        $patch->exchangeArray($options);

        $this->codeRest->updateContentNegotiationConfig($original, $patch);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $this->assertArrayHasKey('zf-content-negotiation', $config);
        $config = $config['zf-content-negotiation'];

        $this->assertArrayHasKey('controllers', $config);
        $this->assertArrayHasKey($original->controllerServiceName, $config['controllers']);
        $this->assertEquals($options['selector'], $config['controllers'][$original->controllerServiceName]);

        $this->assertArrayHasKey('accept-whitelist', $config);
        $this->assertArrayHasKey($original->controllerServiceName, $config['accept-whitelist']);
        $this->assertEquals($options['accept_whitelist'], $config['accept-whitelist'][$original->controllerServiceName]);

        $this->assertArrayHasKey('content-type-whitelist', $config);
        $this->assertArrayHasKey($original->controllerServiceName, $config['content-type-whitelist']);
        $this->assertEquals($options['content_type_whitelist'], $config['content-type-whitelist'][$original->controllerServiceName]);
    }

    public function testUpdateServiceReturnsUpdatedRepresentation()
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $updates = array(
            'route'                      => '/api/bar/foo',
            'page_size'                  => 30,
            'page_size_param'            => 'r',
            'collection_query_whitelist' => array('f', 's'),
            'collection_http_options'    => array('GET'),
            'resource_http_options'      => array('GET'),
            'selector'                   => 'Json',
            'accept_whitelist'           => array('application/json'),
            'content_type_whitelist'     => array('application/json'),
        );
        $patch = new RestEndpointMetadata();
        $patch->exchangeArray(array_merge(array(
            'controller_service_name'    => 'BarConf\Controller\Foo',
        ), $updates));

        $updated = $this->codeRest->updateService($patch);
        $this->assertInstanceOf('ZF\ApiFirstAdmin\Model\RestEndpointMetadata', $updated);

        $values = $updated->getArrayCopy();

        foreach ($updates as $key => $value) {
            $this->assertArrayHasKey($key, $values);
            if ($key === 'route') {
                $this->assertEquals(0, strpos($value, $values[$key]));
                continue;
            }
            $this->assertEquals($value, $values[$key]);
        }
    }
}
