<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Config\Writer\PhpArray as ConfigWriter;
use Zend\Stdlib\ArrayUtils;
use ZF\Apigility\Admin\Model\DbAdapterModel;
use ZF\Configuration\ConfigResource;

class DbAdapterModelTest extends TestCase
{
    public function setUp()
    {
        $this->configPath       = sys_get_temp_dir() . '/zf-apigility-admin/config';
        $this->globalConfigPath = $this->configPath . '/global.php';
        $this->localConfigPath  = $this->configPath . '/local.php';
        $this->removeConfigMocks();
        $this->createConfigMocks();
        $this->configWriter     = new ConfigWriter();
    }

    public function tearDown()
    {
        $this->removeConfigMocks();
    }

    public function createConfigMocks()
    {
        if (!is_dir($this->configPath)) {
            mkdir($this->configPath, 0775, true);
        }

        $contents = "<" . "?php\nreturn array();";
        file_put_contents($this->globalConfigPath, $contents);
        file_put_contents($this->localConfigPath, $contents);
    }

    public function removeConfigMocks()
    {
        if (file_exists($this->globalConfigPath)) {
            unlink($this->globalConfigPath);
        }
        if (file_exists($this->localConfigPath)) {
            unlink($this->localConfigPath);
        }
        if (is_dir($this->configPath)) {
            rmdir($this->configPath);
        }
        if (is_dir(dirname($this->configPath))) {
            rmdir(dirname($this->configPath));
        }
    }

    public function createModelFromConfigArrays(array $global, array $local)
    {
        $this->configWriter->toFile($this->globalConfigPath, $global);
        $this->configWriter->toFile($this->localConfigPath, $local);
        $mergedConfig = ArrayUtils::merge($global, $local);
        $globalConfig = new ConfigResource($mergedConfig, $this->globalConfigPath, $this->configWriter);
        $localConfig  = new ConfigResource($mergedConfig, $this->localConfigPath, $this->configWriter);
        return new DbAdapterModel($globalConfig, $localConfig);
    }

    public function assertDbConfigExists($adapterName, array $config)
    {
        $this->assertArrayHasKey('db', $config);
        $this->assertArrayHasKey('adapters', $config['db']);
        $this->assertArrayHasKey($adapterName, $config['db']['adapters']);
        $this->assertInternalType('array', $config['db']['adapters'][$adapterName]);
    }

    public function assertDbConfigEquals(array $expected, $adapterName, array $config)
    {
        $this->assertDbConfigExists($adapterName, $config);
        $config = $config['db']['adapters'][$adapterName];
        $this->assertEquals($expected, $config);
    }

    public function assertDbConfigContains(array $expected, $adapterName, array $config)
    {
        $this->assertDbConfigExists($adapterName, $config);
        $config = $config['db']['adapters'][$adapterName];
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $config);
            $this->assertEquals($value, $config[$key]);
        }
    }

    /**
     * @group 279
     */
    public function testCreatesBothGlobalAndLocalDbConfigWhenNoneExistedPreviously()
    {
        $toCreate = [
            'driver'   => 'Pdo_Sqlite',
            'database' => __FILE__,
            'dsn'      => '',
        ];

        $model = $this->createModelFromConfigArrays([], []);
        $model->create('Db\New', $toCreate);

        $global = include $this->globalConfigPath;
        $this->assertDbConfigEquals([], 'Db\New', $global);

        $local  = include $this->localConfigPath;
        $this->assertDbConfigEquals([
            'driver'   => 'Pdo_Sqlite',
            'database' => __FILE__,
        ], 'Db\New', $local);
    }

    public function testCreateDoesNotCreateEmptyDsnEntry()
    {
        $toCreate = ['driver' => 'Pdo_Sqlite', 'database' => __FILE__];
        $model    = $this->createModelFromConfigArrays([], []);
        $model->create('Db\New', $toCreate);

        $global = include $this->globalConfigPath;
        $this->assertDbConfigEquals([], 'Db\New', $global);

        $local  = include $this->localConfigPath;
        $this->assertDbConfigEquals($toCreate, 'Db\New', $local);
    }

    public function testCreatesNewEntriesInBothGlobalAndLocalDbConfigWhenConfigExistedPreviously()
    {
        $globalSeedConfig = [
            'db' => [
                'adapters' => [
                    'Db\Old' => [],
                ],
            ],
        ];
        $localSeedConfig = [
            'db' => [
                'adapters' => [
                    'Db\Old' => [
                        'driver'   => 'Pdo_Sqlite',
                        'database' => __FILE__,
                    ],
                ],
            ],
        ];
        $model = $this->createModelFromConfigArrays($globalSeedConfig, $localSeedConfig);
        $model->create('Db\New', ['driver' => 'Pdo_Sqlite', 'database' => __FILE__]);

        $global = include $this->globalConfigPath;
        $this->assertDbConfigEquals([], 'Db\Old', $global);
        $this->assertDbConfigEquals([], 'Db\New', $global);

        $local  = include $this->localConfigPath;
        $this->assertDbConfigEquals($localSeedConfig['db']['adapters']['Db\Old'], 'Db\Old', $local);
        $this->assertDbConfigEquals($localSeedConfig['db']['adapters']['Db\Old'], 'Db\New', $local);
    }

    public function testCanRetrieveListOfAllConfiguredAdapters()
    {
        $globalSeedConfig = [
            'db' => [
                'adapters' => [
                    'Db\Old'   => [],
                    'Db\New'   => [],
                    'Db\Newer' => [],
                ],
            ],
        ];
        $localSeedConfig = [
            'db' => [
                'adapters' => [
                    'Db\Old' => [
                        'driver'   => 'Pdo_Sqlite',
                        'database' => __FILE__,
                    ],
                    'Db\New' => [
                        'driver'   => 'Pdo_Sqlite',
                        'database' => __FILE__,
                    ],
                    'Db\Newer' => [
                        'driver'   => 'Pdo_Sqlite',
                        'database' => __FILE__,
                    ],
                ],
            ],
        ];
        $model        = $this->createModelFromConfigArrays($globalSeedConfig, $localSeedConfig);
        $adapters     = $model->fetchAll();
        $adapterNames = [];
        foreach ($adapters as $adapter) {
            $this->assertInstanceOf('ZF\Apigility\Admin\Model\DbAdapterEntity', $adapter);
            $adapter = $adapter->getArrayCopy();
            $adapterNames[] = $adapter['adapter_name'];
        }
        $this->assertEquals([
            'Db\Old',
            'Db\New',
            'Db\Newer',
        ], $adapterNames);
    }

    public function testCanRetrieveIndividualAdapterDetails()
    {
        $globalSeedConfig = [
            'db' => [
                'adapters' => [
                    'Db\Old'   => [],
                    'Db\New'   => [],
                    'Db\Newer' => [],
                ],
            ],
        ];
        $localSeedConfig = [
            'db' => [
                'adapters' => [
                    'Db\Old' => [
                        'driver'   => 'Pdo_Sqlite',
                        'database' => __FILE__,
                    ],
                    'Db\New' => [
                        'driver'   => 'Pdo_Sqlite',
                        'database' => __FILE__,
                    ],
                    'Db\Newer' => [
                        'driver'   => 'Pdo_Sqlite',
                        'database' => __FILE__,
                    ],
                ],
            ],
        ];
        $model       = $this->createModelFromConfigArrays($globalSeedConfig, $localSeedConfig);
        $adapter     = $model->fetch('Db\New');
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\DbAdapterEntity', $adapter);
        $adapter = $adapter->getArrayCopy();
        $this->assertEquals('Db\New', $adapter['adapter_name']);
        unset($adapter['adapter_name']);
        $this->assertEquals($localSeedConfig['db']['adapters']['Db\New'], $adapter);
    }

    public function testUpdatesLocalDbConfigWhenUpdating()
    {
        $toCreate = ['driver' => 'Pdo_Sqlite', 'database' => __FILE__];
        $model    = $this->createModelFromConfigArrays([], []);
        $model->create('Db\New', $toCreate);

        $newConfig = [
            'driver'   => 'Pdo_Mysql',
            'database' => 'zf_apigility',
            'username' => 'username',
            'password' => 'password',
        ];
        $entity = $model->update('Db\New', $newConfig);

        // Ensure the entity returned from the update is what we expect
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\DbAdapterEntity', $entity);
        $entity = $entity->getArrayCopy();
        $expected = array_merge(['adapter_name' => 'Db\New'], $newConfig);
        $this->assertEquals($expected, $entity);

        // Ensure fetching the entity after an update will return what we expect
        $config = include $this->localConfigPath;
        $this->assertDbConfigEquals($newConfig, 'Db\New', $config);
    }

    public function testRemoveDeletesConfigurationFromBothLocalAndGlobalConfigFiles()
    {
        $toCreate = ['driver' => 'Pdo_Sqlite', 'database' => __FILE__];
        $model    = $this->createModelFromConfigArrays([], []);
        $model->create('Db\New', $toCreate);

        $model->remove('Db\New');
        $global = include $this->globalConfigPath;
        $this->assertArrayNotHasKey('Db\New', $global['db']['adapters']);
        $local = include $this->localConfigPath;
        $this->assertArrayNotHasKey('Db\New', $local['db']['adapters']);
    }

    public function postgresDbTypes()
    {
        return [
            'pdo'    => ['Pdo_Pgsql'],
            'native' => ['Pgsql'],
        ];
    }

    /**
     * @group 184
     * @dataProvider postgresDbTypes
     */
    public function testCreatingPostgresConfigDoesNotIncludeCharset($driver)
    {
        $toCreate = [
            'driver' => $driver,
            'database' => 'test',
            'username' => 'test',
            'password' => 'test',
            'charset' => 'UTF-8',
        ];
        $model    = $this->createModelFromConfigArrays([], []);
        $model->create('Db\New', $toCreate);

        $local  = include $this->localConfigPath;

        $expected = $toCreate;
        unset($expected['charset']);

        $this->assertDbConfigEquals($expected, 'Db\New', $local);
    }

    /**
     * @group 184
     * @dataProvider postgresDbTypes
     */
    public function testUpdatingPostgresConfigDoesNotAllowCharset($driver)
    {
        $toCreate = [
            'driver' => $driver,
            'database' => 'test',
            'username' => 'test',
            'password' => 'test',
            'charset' => 'UTF-8',
        ];
        $model    = $this->createModelFromConfigArrays([], []);
        $model->create('Db\New', $toCreate);

        $newConfig = [
            'driver'   => $driver,
            'database' => 'zf_apigility',
            'username' => 'test',
            'password' => 'test',
            'charset'  => 'latin-1',
        ];
        $entity = $model->update('Db\New', $newConfig);

        // Ensure the entity returned from the update is what we expect
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\DbAdapterEntity', $entity);
        $entity = $entity->getArrayCopy();
        $expected = array_merge(['adapter_name' => 'Db\New'], $newConfig);
        unset($expected['charset']);

        $this->assertEquals($expected, $entity);

        // Ensure fetching the entity after an update will return what we expect
        $config = include $this->localConfigPath;
        unset($expected['adapter_name']);
        $this->assertDbConfigEquals($expected, 'Db\New', $config);
    }
}
