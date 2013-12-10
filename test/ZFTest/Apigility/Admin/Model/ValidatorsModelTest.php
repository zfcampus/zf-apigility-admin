<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use Zend\Validator\ValidatorPluginManager;
use ZF\Apigility\Admin\Model\ValidatorsModel;

class ValidatorsModelTest extends AbstractPluginManagerModelTest
{
    public function setUp()
    {
        $this->plugins = new ValidatorPluginManager();
        $this->model = new ValidatorsModel($this->plugins);
    }
}
