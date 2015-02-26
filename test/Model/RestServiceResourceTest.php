<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use BarConf;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionObject;
use Zend\Config\Writer\PhpArray;
use Zend\EventManager\SharedEventManager;
use ZF\Apigility\Admin\Model\DbConnectedRestServiceModel;
use ZF\Apigility\Admin\Model\ModuleEntity;
use ZF\Apigility\Admin\Model\ModuleModel;
use ZF\Apigility\Admin\Model\ModulePathSpec;
use ZF\Apigility\Admin\Model\RestServiceEntity;
use ZF\Apigility\Admin\Model\RestServiceModel;
use ZF\Apigility\Admin\Model\RestServiceModelFactory;
use ZF\Apigility\Admin\Model\RestServiceResource;
use ZF\Configuration\ResourceFactory;
use ZF\Configuration\ModuleUtils;

class RestServiceResourceTest extends TestCase
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

        $this->moduleEntity = new ModuleEntity($this->module, array(), array(), false);

        $this->moduleManager = $this->getMockBuilder('Zend\ModuleManager\ModuleManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $this->writer        = new PhpArray();
        $moduleUtils         = new ModuleUtils($this->moduleManager);
        $this->modules       = new ModulePathSpec($moduleUtils);
        $this->configFactory = new ResourceFactory($moduleUtils, $this->writer);
        $config = $this->configFactory->factory('BarConf');

        $this->restServiceModel = new RestServiceModel($this->moduleEntity, $this->modules, $config);

        $this->restServiceModelFactory = $this->getMockBuilder('ZF\Apigility\Admin\Model\RestServiceModelFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->restServiceModelFactory
            ->expects($this->any())
            ->method('factory')
            ->with($this->equalTo('BarConf'), $this->equalTo(RestServiceModelFactory::TYPE_DEFAULT))
            ->will($this->returnValue($this->restServiceModel));


        $this->filter        = $this->getMockBuilder('ZF\Apigility\Admin\Model\InputFilterModel')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->docs          = $this->getMockBuilder('ZF\Apigility\Admin\Model\DocumentationModel')
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->resource      = new RestServiceResource($this->restServiceModelFactory, $this->filter, $this->docs);

        $r = new ReflectionObject($this->resource);
        $prop = $r->getProperty('moduleName');
        $prop->setAccessible(true);
        $prop->setValue($this->resource, 'BarConf');
    }

    public function tearDown()
    {
        $this->cleanUpAssets();
    }

    /**
     * @see https://github.com/zfcampus/zf-apigility/issues/18
     */
    public function testCreateReturnsRestServiceEntityWithControllerServiceNamePopulated()
    {
        $entity = $this->resource->create(array('service_name' => 'test'));
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\RestServiceEntity', $entity);
        $controllerServiceName = $entity->controllerServiceName;
        $this->assertNotEmpty($controllerServiceName);
        $this->assertContains('\\Test\\', $controllerServiceName);
    }

    /**
     * @group 166
     */
    public function testPatchOfADbConnectedServiceUpdatesDbConnectedConfiguration()
    {
        $moduleManager           = $this->moduleManager;
        $modulePathSpec          = $this->modules;
        $writer                  = $this->writer;
        $configResourceFactory   = $this->configFactory;
        $moduleModel             = new ModuleModel($moduleManager, array(), array());
        $sharedEvents            = new SharedEventManager();
        $restServiceModelFactory = new RestServiceModelFactory(
            $modulePathSpec,
            $configResourceFactory,
            $sharedEvents,
            $moduleModel
        );
        $resource                = new RestServiceResource($restServiceModelFactory, $this->filter, $this->docs);

        $sharedEvents->attach(
            'ZF\Apigility\Admin\Model\RestServiceModel',
            'fetch',
            'ZF\Apigility\Admin\Model\DbConnectedRestServiceModel::onFetch'
        );

        $r = new ReflectionObject($resource);
        $prop = $r->getProperty('moduleName');
        $prop->setAccessible(true);
        $prop->setValue($resource, 'BarConf');

        $entity = $resource->create(array(
            'adapter_name' => 'Db\Test',
            'table_name'   => 'test',
        ));
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\DbConnectedRestServiceEntity', $entity);

        $id = $entity->controllerServiceName;
        $updateData = array(
            'entity_identifier_name' => 'test_id',
            'hydrator_name' => 'Zend\Stdlib\Hydrator\ObjectProperty',
        );
        $resource->patch($id, $updateData);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('BarConf\\V1\\Rest\\Test\\TestEntity', $config['zf-hal']['metadata_map']);
        $this->assertArrayHasKey('BarConf\\V1\\Rest\\Test\\TestResource', $config['zf-apigility']['db-connected']);
        $halConfig = $config['zf-hal']['metadata_map']['BarConf\\V1\\Rest\\Test\\TestEntity'];
        $agConfig  = $config['zf-apigility']['db-connected']['BarConf\\V1\\Rest\\Test\\TestResource'];

        $this->assertEquals('test_id', $halConfig['entity_identifier_name']);
        $this->assertEquals('test_id', $agConfig['entity_identifier_name']);
        $this->assertEquals('Zend\\Stdlib\\Hydrator\\ObjectProperty', $halConfig['hydrator']);
        $this->assertEquals('Zend\\Stdlib\\Hydrator\\ObjectProperty', $agConfig['hydrator_name']);
    }
}
