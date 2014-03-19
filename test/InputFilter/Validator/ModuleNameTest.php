<?php
namespace ZFTest\Apigility\Admin\InputFilter\Validator;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\InputFilter\Validator\ModuleNameValidator;

class ModuleNameTest extends TestCase
{
    public function testValidModuleName()
    {
        $validator = new ModuleNameValidator();
        $this->assertTrue($validator->isValid('test'));
        $this->assertTrue($validator->isValid('test_test'));
        $this->assertTrue($validator->isValid('test0'));
    }

    public function testNotValidModuleName()
    {
        $validator = new ModuleNameValidator();
        $this->assertFalse($validator->isValid('eval'));
        $this->assertFalse($validator->isValid('Eval'));
        $this->assertFalse($validator->isValid('0test'));
    }
}
