<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use AuthConf;
use AuthConfDefaults;
use AuthConfWithConfig;
use FooConf;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Config\Writer\PhpArray;
use ZF\Apigility\Admin\Model\AuthorizationEntity;
use ZF\Apigility\Admin\Model\AuthorizationModel;
use ZF\Apigility\Admin\Model\ModuleEntity;
use ZF\Apigility\Admin\Model\ModulePathSpec;
use ZF\Configuration\ResourceFactory;
use ZF\Configuration\ModuleUtils;

class AuthorizationModelTest extends TestCase
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

    public function setUpModel($module)
    {
        $this->module = $module;
        $this->cleanUpAssets();

        $modules = array(
            'FooConf'            => new FooConf\Module(),
            'AuthConf'           => new AuthConf\Module(),
            'AuthConfDefaults'   => new AuthConfDefaults\Module(),
            'AuthConfWithConfig' => new AuthConfWithConfig\Module(),
        );

        $this->moduleEntity  = new ModuleEntity($this->module);
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
        $this->model    = new AuthorizationModel(
            $this->moduleEntity,
            $this->modules,
            $this->resource->factory($this->module)
        );
    }

    public function tearDown()
    {
        $this->cleanUpAssets();
    }

    public function assertDefaultPrivileges(array $privileges)
    {
        $this->assertEquals(array(
            'GET' => false,
            'POST' => false,
            'PUT' => false,
            'PATCH' => false,
            'DELETE' => false,
        ), $privileges);
    }

    protected function mapConfigToPayload(array $config)
    {
        foreach ($config as $key => $value) {
            // Replace keys to match what the API is going to send back and forth
            if (isset($value['actions'])) {
                foreach ($value['actions'] as $action => $privileges) {
                    $newKey = sprintf('%s::%s', $key, $action);
                    $config[$newKey] = $privileges;
                }
            }
            if (isset($value['entity'])) {
                $newKey = sprintf('%s::__entity__', $key);
                $config[$newKey] = $value['entity'];
            }
            if (isset($value['collection'])) {
                $newKey = sprintf('%s::__collection__', $key);
                $config[$newKey] = $value['collection'];
            }
            unset ($config[$key]);
        }
        return $config;
    }

    protected function mapEntityToConfig(AuthorizationEntity $entity)
    {
        $normalized = array();
        foreach ($entity->getArrayCopy() as $spec => $privileges) {
            preg_match('/^(?P<service>[^:]+)(::(?P<action>.*))?$/', $spec, $matches);
            if (!isset($matches['action'])) {
                $normalized[$matches['service']]['actions']['index'] = $privileges;
            } elseif (preg_match('/^__(?P<type>collection|entity)__$/', $matches['action'], $actionMatches)) {
                $type = $actionMatches['type'];
                $normalized[$matches['service']][$type] = $privileges;
            } else {
                $normalized[$matches['service']]['actions'][$matches['action']] = $privileges;
            }
        }
        return $normalized;
    }

    public function testFetchReturnsEmptyAuthorizationEntityWhenNoServicesPresent()
    {
        $this->setUpModel('FooConf');
        $entity = $this->model->fetch();
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\AuthorizationEntity', $entity);
        $this->assertEquals(0, count($entity));
    }

    public function testFetchReturnsPopulatedAuthorizationEntityWhenServicesArePresent()
    {
        $this->setUpModel('AuthConf');
        $entity = $this->model->fetch();
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\AuthorizationEntity', $entity);
        $this->assertEquals(6, count($entity));
        $expected = array(
            'AuthConf\V1\Rest\Foo\Controller::__entity__',
            'AuthConf\V1\Rest\Foo\Controller::__collection__',
            'AuthConf\V1\Rest\Bar\Controller::__entity__',
            'AuthConf\V1\Rest\Bar\Controller::__collection__',
            'AuthConf\V1\Rpc\Baz\Controller::baz',
            'AuthConf\V1\Rpc\Bat\Controller::bat',
        );
        $actual = array();
        foreach ($entity as $serviceName => $privileges) {
            $actual[] = $serviceName;
            $this->assertDefaultPrivileges($privileges);
        }
        $this->assertEquals($expected, $actual);
    }

    public function testUsesIndexAsActionForRpcServicesIfActionCannotBeDetermined()
    {
        $this->setUpModel('AuthConfDefaults');
        $entity = $this->model->fetch();
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\AuthorizationEntity', $entity);
        $this->assertEquals(6, count($entity));
        $this->assertTrue($entity->has('AuthConfDefaults\V1\Rpc\Bat\Controller::index'));
    }

    public function testFetchAcceptsVersionAndReturnsAuthorizationListByVersion()
    {
        $this->setUpModel('AuthConf');
        $entity = $this->model->fetch(2); // <- VERSION!
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\AuthorizationEntity', $entity);
        $this->assertEquals(9, count($entity));
        $expected = array(
            'AuthConf\V2\Rest\Foo\Controller::__entity__',
            'AuthConf\V2\Rest\Foo\Controller::__collection__',
            'AuthConf\V2\Rest\Bar\Controller::__entity__',
            'AuthConf\V2\Rest\Bar\Controller::__collection__',
            'AuthConf\V2\Rest\New\Controller::__entity__',
            'AuthConf\V2\Rest\New\Controller::__collection__',
            'AuthConf\V2\Rpc\Baz\Controller::baz',
            'AuthConf\V2\Rpc\Bat\Controller::bat',
            'AuthConf\V2\Rpc\New\Controller::new',
        );
        $actual = array();
        foreach ($entity as $serviceName => $privileges) {
            $actual[] = $serviceName;
            $this->assertDefaultPrivileges($privileges);
        }
        $this->assertEquals($expected, $actual);
    }

    public function testAccuratelyRepresentsExistingPrivileges()
    {
        $this->setUpModel('AuthConfWithConfig');

        // Get config as it exists to begin
        $config = $this->resource->factory($this->module)->fetch(true);
        $config = $config['zf-mvc-auth']['authorization'];

        // Have the model fetch it
        $entity = $this->model->fetch();
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\AuthorizationEntity', $entity);
        $entity = $this->mapEntityToConfig($entity);
        $this->assertEquals($config, $entity);
    }

    public function testCanUpdatePrivileges()
    {
        $this->setUpModel('AuthConfWithConfig');

        // Get config as it exists to begin
        $config = $this->resource->factory($this->module)->fetch(true);

        // Toggle all privileges
        $newPrivileges = $this->mapConfigToPayload($config['zf-mvc-auth']['authorization']);
        foreach ($newPrivileges as $serviceName => $privileges) {
            foreach ($privileges as $method => $flag) {
                $newPrivileges[$serviceName][$method] = ! $flag;
            }
        }

        $entity = $this->model->update($newPrivileges);
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\AuthorizationEntity', $entity);

        // Test that the entity matches the new privileges
        $this->assertEquals($newPrivileges, $entity->getArrayCopy());

        // Test that the stored configuration has been updated as well
        $config = $this->resource->factory($this->module)->fetch(true);
        $config = $config['zf-mvc-auth']['authorization'];

        $expected = $this->mapEntityToConfig($entity);

        $this->assertEquals($expected, $config);
    }
}
