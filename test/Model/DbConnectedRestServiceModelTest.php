<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use BarConf;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;
use Zend\Config\Writer\PhpArray;
use Zend\EventManager\Event;
use ZF\Apigility\Admin\Model\DbConnectedRestServiceModel;
use ZF\Apigility\Admin\Model\DbConnectedRestServiceEntity;
use ZF\Apigility\Admin\Model\ModuleEntity;
use ZF\Apigility\Admin\Model\ModulePathSpec;
use ZF\Apigility\Admin\Model\RestServiceEntity;
use ZF\Apigility\Admin\Model\RestServiceModel;
use ZF\Configuration\ResourceFactory;
use ZF\Configuration\ModuleUtils;

class DbConnectedRestServiceModelTest extends TestCase
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
        foreach (glob(sprintf('%s/src/%s/V*', $basePath, $this->module)) as $dir) {
            $this->removeDir($dir);
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
        $this->model    = new DbConnectedRestServiceModel($this->codeRest);
        $this->codeRest->getEventManager()->attach('fetch', array($this->model, 'onFetch'));
    }

    public function tearDown()
    {
        $this->cleanUpAssets();
    }

    public function getCreationPayload()
    {
        $payload = new DbConnectedRestServiceEntity();
        $payload->exchangeArray(array(
            'adapter_name'               => 'DB\Barbaz',
            'table_name'                 => 'barbaz',
            'hydrator_name'              => 'ObjectProperty',
            'entity_identifier_name'     => 'barbaz_id',
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

    public function testCreateServiceReturnsDbConnectedRestServiceEntity()
    {
        $originalEntity = $this->getCreationPayload();
        $result         = $this->model->createService($originalEntity);
        $this->assertSame($originalEntity, $result);

        $this->assertEquals('BarConf\V1\Rest\Barbaz\Controller', $result->controllerServiceName);
        $this->assertEquals('BarConf\V1\Rest\Barbaz\BarbazResource', $result->resourceClass);
        $this->assertEquals('BarConf\V1\Rest\Barbaz\BarbazEntity', $result->entityClass);
        $this->assertEquals('BarConf\V1\Rest\Barbaz\BarbazCollection', $result->collectionClass);
        $this->assertEquals('BarConf\V1\Rest\Barbaz\BarbazResource\Table', $result->tableService);
        $this->assertEquals('barbaz', $result->tableName);
        $this->assertEquals('bar-conf.rest.barbaz', $result->routeName);
    }

    public function testEntityCreatedViaCreateServiceIsAnArrayObjectExtension()
    {
        $originalEntity = $this->getCreationPayload();
        $result         = $this->model->createService($originalEntity);
        include __DIR__ . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Barbaz/BarbazEntity.php';
        $r = new ReflectionClass('BarConf\V1\Rest\Barbaz\BarbazEntity');
        $parent = $r->getParentClass();
        $this->assertInstanceOf('ReflectionClass', $parent);
        $this->assertEquals('ArrayObject', $parent->getName());
    }

    public function testCreateServiceWritesDbConnectedConfigurationUsingResourceClassAsKey()
    {
        $originalEntity = $this->getCreationPayload();
        $result         = $this->model->createService($originalEntity);
        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';

        $this->assertArrayHasKey('zf-apigility', $config);
        $this->assertArrayHasKey('db-connected', $config['zf-apigility']);
        $this->assertArrayHasKey($result->resourceClass, $config['zf-apigility']['db-connected']);

        $resourceConfig = $config['zf-apigility']['db-connected'][$result->resourceClass];
        $this->assertArrayHasKey('table_name', $resourceConfig);
        $this->assertArrayHasKey('hydrator_name', $resourceConfig);
        $this->assertArrayHasKey('controller_service_name', $resourceConfig);

        $this->assertEquals('barbaz', $resourceConfig['table_name']);
        $this->assertEquals($result->hydratorName, $resourceConfig['hydrator_name']);
        $this->assertEquals($result->controllerServiceName, $resourceConfig['controller_service_name']);
    }

    public function testCreateServiceWritesRestConfigurationWithEntityAndCollectionClass()
    {
        $originalEntity = $this->getCreationPayload();
        $result         = $this->model->createService($originalEntity);
        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';

        $this->assertArrayHasKey('zf-rest', $config);
        $this->assertArrayHasKey($result->controllerServiceName, $config['zf-rest']);

        $restConfig = $config['zf-rest'][$result->controllerServiceName];
        $this->assertArrayHasKey('entity_class', $restConfig);
        $this->assertArrayHasKey('collection_class', $restConfig);

        $this->assertEquals($result->entityClass, $restConfig['entity_class']);
        $this->assertEquals($result->collectionClass, $restConfig['collection_class']);
    }

    public function testCreateServiceWritesHalConfigurationWithHydrator()
    {
        $originalEntity = $this->getCreationPayload();
        $result         = $this->model->createService($originalEntity);
        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';

        $this->assertArrayHasKey('zf-hal', $config);
        $this->assertArrayHasKey('metadata_map', $config['zf-hal']);
        $this->assertArrayHasKey($result->entityClass, $config['zf-hal']['metadata_map']);

        $halConfig = $config['zf-hal']['metadata_map'][$result->entityClass];
        $this->assertArrayHasKey('hydrator', $halConfig);

        $this->assertEquals($result->hydratorName, $halConfig['hydrator']);
    }

    public function testCreateServiceDoesNotCreateResourceClass()
    {
        $originalEntity = $this->getCreationPayload();
        $result         = $this->model->createService($originalEntity);
        $this->assertFalse(
            file_exists(__DIR__ . '/TestAsset/module/BarConf/src/BarConf/Rest/Barbaz/BarbazResource.php')
        );
    }

    public function testOnFetchWillRecastEntityToDbConnectedIfDbConnectedConfigurationExists()
    {
        $originalData = array(
            'controller_service_name' => 'BarConf\Rest\Barbaz\Controller',
            'resource_class'          => 'BarConf\Rest\Barbaz\BarbazResource',
            'route_name'              => 'bar-conf.rest.barbaz',
            'route_match'             => '/api/barbaz',
            'entity_class'            => 'BarConf\Rest\Barbaz\BarbazEntity',
        );
        $entity = new RestServiceEntity();
        $entity->exchangeArray($originalData);
        $config = array( 'zf-apigility' => array('db-connected' => array(
            'BarConf\Rest\Barbaz\BarbazResource' => array(
                'adapter_name'  => 'Db\Barbaz',
                'table_name'    => 'barbaz',
                'hydrator_name' => 'ObjectProperty',
            ),
        )));

        $event = new Event();
        $event->setParam('entity', $entity);
        $event->setParam('config', $config);
        $result = $this->model->onFetch($event);
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\DbConnectedRestServiceEntity', $result);
        $asArray = $result->getArrayCopy();
        foreach ($originalData as $key => $value) {
            $this->assertArrayHasKey($key, $asArray);
            if ($key === 'resource_class') {
                $this->assertNull(
                    $asArray[$key],
                    sprintf("Failed asserting that resource_class is null\nEntity is: %s\n", var_export($asArray, 1))
                );
                continue;
            }
            $this->assertEquals(
                $value,
                $asArray[$key],
                sprintf("Failed testing key '%s'\nEntity is: %s\n", $key, var_export($asArray, 1))
            );
        }
        foreach ($config['zf-apigility']['db-connected']['BarConf\Rest\Barbaz\BarbazResource'] as $key => $value) {
            $this->assertArrayHasKey($key, $asArray);
            $this->assertEquals($value, $asArray[$key]);
        }
        $this->assertArrayHasKey('table_service', $asArray);
        $this->assertEquals($entity->resourceClass . '\\Table', $asArray['table_service']);
    }

    /**
     * @group 166
     */
    public function testOnFetchWillRetainResourceClassIfEventFetchFlagIsFalse()
    {
        $originalData = array(
            'controller_service_name' => 'BarConf\Rest\Barbaz\Controller',
            'resource_class'          => 'BarConf\Rest\Barbaz\BarbazResource',
            'route_name'              => 'bar-conf.rest.barbaz',
            'route_match'             => '/api/barbaz',
            'entity_class'            => 'BarConf\Rest\Barbaz\BarbazEntity',
        );
        $entity = new RestServiceEntity();
        $entity->exchangeArray($originalData);
        $config = array( 'zf-apigility' => array('db-connected' => array(
            'BarConf\Rest\Barbaz\BarbazResource' => array(
                'adapter_name'  => 'Db\Barbaz',
                'table_name'    => 'barbaz',
                'hydrator_name' => 'ObjectProperty',
            ),
        )));

        $event = new Event();
        $event->setParam('entity', $entity);
        $event->setParam('config', $config);
        $event->setParam('fetch', false);
        $result = $this->model->onFetch($event);

        $this->assertInstanceOf('ZF\Apigility\Admin\Model\DbConnectedRestServiceEntity', $result);
        $this->assertEquals('BarConf\Rest\Barbaz\BarbazResource', $result->resourceClass);
        $asArray = $result->getArrayCopy();
        $this->assertArrayHasKey('resource_class', $asArray);
        $this->assertEquals('BarConf\Rest\Barbaz\BarbazResource', $asArray['resource_class']);
    }

    public function testUpdateServiceReturnsUpdatedDbConnectedRestServiceEntity()
    {
        $originalEntity = $this->getCreationPayload();
        $this->model->createService($originalEntity);

        $newProps = array(
            'table_service' => 'My\Custom\Table',
            'adapter_name'  => 'My\Db',
            'hydrator_name' => 'ClassMethods',
        );
        $originalEntity->exchangeArray($newProps);
        $result = $this->model->updateService($originalEntity);

        $this->assertInstanceOf('ZF\Apigility\Admin\Model\DbConnectedRestServiceEntity', $result);
        $this->assertNotSame($originalEntity, $result);
        $this->assertEquals($newProps['table_service'], $result->tableService);
        $this->assertEquals($newProps['adapter_name'], $result->adapterName);
        $this->assertEquals($newProps['hydrator_name'], $result->hydratorName);
    }

    public function testUpdateServiceUpdatesDbConnectedConfiguration()
    {
        $originalEntity = $this->getCreationPayload();
        $this->model->createService($originalEntity);

        $newProps = array(
            'table_service' => 'My\Custom\Table',
            'adapter_name'  => 'My\Db',
            'hydrator_name' => 'ClassMethods',
        );
        $originalEntity->exchangeArray($newProps);
        $result = $this->model->updateService($originalEntity);

        $this->assertInstanceOf('ZF\Apigility\Admin\Model\DbConnectedRestServiceEntity', $result);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $this->assertArrayHasKey('zf-apigility', $config);
        $this->assertArrayHasKey('db-connected', $config['zf-apigility']);
        $this->assertArrayHasKey('BarConf\V1\Rest\Barbaz\BarbazResource', $config['zf-apigility']['db-connected']);

        $resourceConfig = $config['zf-apigility']['db-connected']['BarConf\V1\Rest\Barbaz\BarbazResource'];
        $this->assertArrayHasKey('adapter_name', $resourceConfig);
        $this->assertArrayHasKey('table_service', $resourceConfig);
        $this->assertArrayHasKey('table_name', $resourceConfig);
        $this->assertArrayHasKey('hydrator_name', $resourceConfig);

        $this->assertEquals($newProps['adapter_name'], $resourceConfig['adapter_name']);
        $this->assertEquals($newProps['table_service'], $resourceConfig['table_service']);
        $this->assertEquals('barbaz', $resourceConfig['table_name']);
        $this->assertEquals($newProps['hydrator_name'], $resourceConfig['hydrator_name']);
    }

    /**
     * @group 166
     */
    public function testUpdateServiceUpdatesEntityIdentifierNameAndHydrator()
    {
        $originalEntity = $this->getCreationPayload();
        $this->model->createService($originalEntity);

        $newProps = array(
            'entity_identifier_name' => 'id',
            'hydrator_name'          => 'Zend\\Stdlib\\Hydrator\\ClassMethods',
        );
        $originalEntity->exchangeArray($newProps);
        $result = $this->model->updateService($originalEntity);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $this->assertArrayHasKey('zf-apigility', $config);
        $this->assertArrayHasKey('db-connected', $config['zf-apigility']);
        $this->assertArrayHasKey('BarConf\V1\Rest\Barbaz\BarbazResource', $config['zf-apigility']['db-connected']);

        $resourceConfig = $config['zf-apigility']['db-connected']['BarConf\V1\Rest\Barbaz\BarbazResource'];
        $this->assertEquals($newProps['entity_identifier_name'], $resourceConfig['entity_identifier_name']);
        $this->assertEquals($newProps['hydrator_name'], $resourceConfig['hydrator_name']);
    }

    public function testDeleteServiceRemovesDbConnectedConfigurationForEntity()
    {
        $originalEntity = $this->getCreationPayload();
        $this->model->createService($originalEntity);
        $this->model->deleteService($originalEntity);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $barbazPath = __DIR__ . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Barbaz';

        $this->assertTrue(file_exists($barbazPath));
        $this->assertArrayHasKey('zf-apigility', $config);
        $this->assertArrayHasKey('db-connected', $config['zf-apigility']);
        $this->assertArrayNotHasKey($originalEntity->resourceClass, $config['zf-apigility']['db-connected']);
    }

    public function testDeleteServiceRecursive()
    {
        $originalEntity = $this->getCreationPayload();
        $this->model->createService($originalEntity);
        $this->model->deleteService($originalEntity, true);

        $barbazPath = __DIR__ . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Barbaz';
        $this->assertFalse(file_exists($barbazPath));
    }

    public function testCreateServiceWithUnderscoreInNameNormalizesClassNamesToCamelCase()
    {
        $originalEntity = $this->getCreationPayload();
        $originalEntity->exchangeArray(array('table_name' => 'bar_baz'));

        $result = $this->model->createService($originalEntity);
        $this->assertSame($originalEntity, $result);

        $this->assertEquals('BarConf\V1\Rest\BarBaz\Controller', $result->controllerServiceName);
        $this->assertEquals('BarConf\V1\Rest\BarBaz\BarBazResource', $result->resourceClass);
        $this->assertEquals('BarConf\V1\Rest\BarBaz\BarBazEntity', $result->entityClass);
        $this->assertEquals('BarConf\V1\Rest\BarBaz\BarBazCollection', $result->collectionClass);
        $this->assertEquals('BarConf\V1\Rest\BarBaz\BarBazResource\Table', $result->tableService);
        $this->assertEquals('bar_baz', $result->tableName);
        $this->assertEquals('bar-conf.rest.bar-baz', $result->routeName);
    }
}
