<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Validator\ValidatorPluginManager;
use ZF\Apigility\Admin\Model\ValidatorsModel;

class ValidatorsModelTest extends TestCase
{
    public function setUp()
    {
        $this->plugins = new ValidatorPluginManager();
        $this->model = new ValidatorsModel($this->plugins);
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
