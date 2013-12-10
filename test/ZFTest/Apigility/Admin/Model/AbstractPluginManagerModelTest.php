<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use PHPUnit_Framework_TestCase as TestCase;

abstract class AbstractPluginManagerModelTest extends TestCase
{
    public $plugins;
    public $model;

    public function setUp()
    {
        $this->markTestIncomplete(
            'Please define the setUp() method in your extending test case, and set the plugins and model properties'
        );
    }

    public function testFetchAllReturnsListOfAvailablePlugins()
    {
        $allServices = $this->plugins->getRegisteredServices();
        $validators  = [];
        foreach ($allServices as $key => $services) {
            $validators += $services;
        }
        sort($validators, SORT_STRING);

        $this->assertEquals($validators, $this->model->fetchAll());
    }
}
