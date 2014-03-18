<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\Model\ModuleEntity;

class ModuleEntityTest extends TestCase
{
    public function testCanSetAndRetrieveModuleDefaultVersion()
    {
        $moduleEntity = new ModuleEntity('Test\Foo');
        $this->assertSame(1, $moduleEntity->getDefaultVersion()); // initial state

        $moduleEntity->exchangeArray(array('default_version' => 123));
        $this->assertSame(123, $moduleEntity->getDefaultVersion());
    }
}
