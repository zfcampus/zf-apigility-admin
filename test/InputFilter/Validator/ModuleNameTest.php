<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\InputFilter\Validator;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\InputFilter\Validator\ModuleNameValidator;

class ModuleNameTest extends TestCase
{
    public function validModuleNames()
    {
        return [
            'string' => ['test'],
            'string-with-underscores' => ['test_test'],
            'string-with-digits' => ['test0'],
        ];
    }

    public function invalidModuleNames()
    {
        return [
            'eval' => ['eval'],
            'Eval' => ['Eval'],
            'digit-leading' => ['0test'],
        ];
    }

    /**
     * @dataProvider validModuleNames
     */
    public function testValidModuleName($name)
    {
        $validator = new ModuleNameValidator();
        $this->assertTrue($validator->isValid($name));
    }

    /**
     * @dataProvider invalidModuleNames
     */
    public function testInvalidModuleName($name)
    {
        $validator = new ModuleNameValidator();
        $this->assertFalse($validator->isValid($name));
    }
}
