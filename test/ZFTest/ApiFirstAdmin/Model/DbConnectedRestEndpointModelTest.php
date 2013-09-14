<?php

namespace ZFTest\ApiFirstAdmin\Model;

use BarConf;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;
use Zend\Config\Writer\PhpArray;
use ZF\ApiFirstAdmin\Model\DbConnectedRestEndpointModel;
use ZF\ApiFirstAdmin\Model\DbConnectedRestEndpointEntity;
use ZF\ApiFirstAdmin\Model\RestEndpointModel;
use ZF\Configuration\ResourceFactory;
use ZF\Configuration\ModuleUtils;

require_once __DIR__ . '/TestAsset/module/BarConf/Module.php';

class DbConnectedRestEndpointModelTest extends TestCase
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
        $this->codeRest = new RestEndpointModel($this->module, $this->modules, $this->resource->factory('BarConf'));
        $this->model    = new DbConnectedRestEndpointModel($this->codeRest);
    }

    public function tearDown()
    {
        $this->cleanUpAssets();
    }

    public function getCreationPayload()
    {
        $payload = new DbConnectedRestEndpointEntity();
        $payload->exchangeArray(array(
            'adapter_name'               => 'DB\Foo',
            'table_name'                 => 'foo',
            'hydrator_name'              => 'ObjectProperty',
            'resource_http_methods'      => array('GET', 'PATCH'),
            'collection_http_methods'    => array('GET', 'POST'),
            'collection_query_whitelist' => array('sort', 'filter'),
            'page_size'                  => 10,
            'page_size_param'            => 'p',
            'selector'                   => 'HalJson',
            'accept_whitelist'           => array('application/json', 'application/*+json'),
            'content_type_whitelist'     => array('application/json'),
        ));
        return $payload;
    }

    public function testCreateServiceReturnsDbConnectedRestEndpointEntity()
    {
        $originalEntity = $this->getCreationPayload();
        $result         = $this->model->createService($originalEntity);
        $this->assertSame($originalEntity, $result);

        $this->assertEquals('BarConf\Rest\Foo\Controller', $result->controllerServiceName);
        $this->assertEquals('BarConf\Rest\Foo\FooResource', $result->resourceClass);
        $this->assertEquals('BarConf\Rest\Foo\FooEntity', $result->entityClass);
        $this->assertEquals('BarConf\Rest\Foo\FooCollection', $result->collectionClass);
        $this->assertEquals('BarConf\Rest\Foo\FooResource\Table', $result->tableService);
        $this->assertEquals('foo', $result->tableName);
        $this->assertEquals('bar-conf.rest.foo', $result->routeName);
    }

    public function testCreateServiceWritesDbConnectedConfigurationUsingResourceClassAsKey()
    {
        $originalEntity = $this->getCreationPayload();
        $result         = $this->model->createService($originalEntity);
        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';

        $this->assertArrayHasKey('zf-api-first', $config);
        $this->assertArrayHasKey('db-connected', $config['zf-api-first']);
        $this->assertArrayHasKey($result->resourceClass, $config['zf-api-first']['db-connected']);

        $resourceConfig = $config['zf-api-first']['db-connected'][$result->resourceClass];
        $this->assertArrayHasKey('table_name', $resourceConfig);
        $this->assertArrayHasKey('hydrator_name', $resourceConfig);

        $this->assertEquals('foo', $resourceConfig['table_name']);
        $this->assertEquals($result->hydratorName, $resourceConfig['hydrator_name']);
    }

    public function testCreateServiceDoesNotCreateResourceClass()
    {
        $originalEntity = $this->getCreationPayload();
        $result         = $this->model->createService($originalEntity);
        $this->assertFalse(file_exists(__DIR__ . '/TestAsset/module/BarConf/src/BarConf/Rest/Foo/FooResource.php'));
    }
}
