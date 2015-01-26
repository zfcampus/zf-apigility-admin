<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use PHPUnit_Framework_TestCase as TestCase;

abstract class AbstractPluginManagerModelTest extends TestCase
{
    public $model;
    public $namespace;
    public $plugins;

    public function setUp()
    {
        $this->markTestIncomplete(
            'Please define the setUp() method in your extending test case,'
            . ' and set the plugins, model, and namespace properties'
        );
    }

    public function testFetchAllReturnsListOfAvailablePlugins()
    {
        $services = $this->model->fetchAll();
        $this->assertGreaterThan(0, count($services));
        foreach ($services as $service) {
            $this->assertContains($this->namespace, $service);
        }
    }
}
