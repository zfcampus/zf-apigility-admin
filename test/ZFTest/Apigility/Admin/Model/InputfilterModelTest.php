<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\Model\InputfilterModel;
use ZF\Configuration\ResourceFactory as ConfigResourceFactory;

class InputfilterModelTest extends TestCase
{
    public function setUp()
    {
        // @todo create a ConfigResourceFactory for testing
        //$this->configFactory = new ConfigResourceFactory();
        $this->model  = new InputfilterModel($this->configFactory);
    }

}
