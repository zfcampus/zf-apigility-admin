<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use Zend\Hydrator\HydratorPluginManager;
use ZF\Apigility\Admin\Model\HydratorsModel;

class HydratorsModelTest extends AbstractPluginManagerModelTest
{
    public function setUp()
    {
        $this->namespace = '\\Hydrator\\';
        $this->plugins = new HydratorPluginManager();
        $this->model = new HydratorsModel($this->plugins);
    }

    public function testFetchAllReturnsListOfAvailablePlugins()
    {
        $services = $this->model->fetchAll();
        $this->assertGreaterThan(-1, count($services));
        foreach ($services as $service) {
            $this->assertContains($this->namespace, $service);
        }
    }
}
