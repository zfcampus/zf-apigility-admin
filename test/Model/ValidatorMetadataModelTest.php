<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\Model\ValidatorMetadataModel;

class ValidatorMetadataModelTest extends TestCase
{
    protected $config;

    public function setUp()
    {
        $this->getConfig();
        $this->model = new ValidatorMetadataModel($this->config);
    }

    public function getConfig()
    {
        if (is_array($this->config)) {
            return $this->config;
        }

        $configFile = __DIR__ . '/../../config/module.config.php';
        if (!file_exists($configFile)) {
            $this->markTestSkipped('Cannot find module config file!');
        }
        $allConfig = include $configFile;
        if (!array_key_exists('validator_metadata', $allConfig)) {
            $this->markTestSkipped('Module config file does not contain validator_metadata!');
        }

        $this->config = $allConfig['validator_metadata'];
        return $this->config;
    }

    public function assertDefaultOptions(array $metadata)
    {
        foreach (array_keys($this->config['__all__']) as $key) {
            $this->assertArrayHasKey($key, $metadata);
        }
    }

    public function allPlugins()
    {
        $return = array();
        foreach ($this->getConfig() as $plugin => $data) {
            if ('__all__' == $plugin) {
                continue;
            }
            $return[$plugin] = array($plugin);
        }
        return $return;
    }

    /**
     * @dataProvider allPlugins
     */
    public function testAllPluginsContainDefaultOptions($plugin)
    {
        $metadata = $this->model->fetch($plugin);
        $this->assertInternalType('array', $metadata);
        $this->assertDefaultOptions($metadata);
    }

    /**
     * @dataProvider allPlugins
     */
    public function testCanFetchAllMetadataAtOnce($plugin)
    {
        $metadata = $this->model->fetchAll();
        $this->assertInternalType('array', $metadata);
        $this->assertArrayHasKey($plugin, $metadata);
    }

    /**
     * @dataProvider allPlugins
     */
    public function testEachPluginInAllMetadataContainsDefaultOptions($plugin)
    {
        $metadata = $this->model->fetchAll();
        $this->assertInternalType('array', $metadata);
        $this->assertArrayHasKey($plugin, $metadata);

        $metadata = $metadata[$plugin];
        $this->assertInternalType('array', $metadata);
        $this->assertDefaultOptions($metadata);
    }

    public function testFetchingAllMetadataOmitsMagicAllKey()
    {
        $metadata = $this->model->fetchAll();
        $this->assertInternalType('array', $metadata);
        $this->assertArrayNotHasKey('__all__', $metadata);
    }
}
