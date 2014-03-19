<?php

namespace ZFTest\Apigility\Admin\InputFilter;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\InputFilter\ModuleInputFilter;

class ModuleInputFilterTest extends TestCase
{
    /**
     * @dataProvider dataProviderIsValidTrue
     */
    public function testIsValidTrue($data)
    {
        $i = new ModuleInputFilter;
        $i->setData($data);
        $this->assertTrue($i->isValid());
    }

    public function dataProviderIsValidTrue()
    {
        return array(
            array(
                array('name' => 'Foo')
            ),
            array(
                array('name' => 'My_Status')
            ),
        );
    }

    /**
     * @dataProvider dataProviderIsValidFalse
     */
    public function testIsValidFalse($data, $messages)
    {
        $i = new ModuleInputFilter;
        $i->setData($data);
        $this->assertFalse($i->isValid());
        $this->assertEquals($messages, $i->getMessages());
    }

    public function dataProviderIsValidFalse()
    {
        return array(
            array(
                array('name' => '_'),
                array('name' => array('api_name' => "'_' is not a valid api name"))
            ),
            array(
                array('name' => 'My\Status'),
                array('name' => array('api_name' => "'My\Status' is not a valid api name"))
            ),
        );
    }
}
 