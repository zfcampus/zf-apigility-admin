<?php

namespace ZFTest\Apigility\Admin\InputFilter\RestService;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\InputFilter\RestService\PostInputFilter;

class PostInputFilterTest extends TestCase
{
    /**
     * @dataProvider dataProviderIsValidTrue
     */
    public function testIsValidTrue($data)
    {
        $i = new PostInputFilter;
        $i->setData($data);
        $this->assertTrue($i->isValid());
    }

    public function dataProviderIsValidTrue()
    {
        return array(
            array(
                array('service_name' => 'Foo')
            ),
            array(
                array('adapter_name' => 'Status', 'table_name' => 'foo')
            )
        );
    }

    /**
     * @dataProvider dataProviderIsValidFalse
     */
    public function testIsValidFalse($data, $messages)
    {
        $i = new PostInputFilter;
        $i->setData($data);
        $this->assertFalse($i->isValid());
        $this->assertEquals($messages, $i->getMessages());
    }

    public function dataProviderIsValidFalse()
    {
        return array(
            // no values
            array(
                array(),
                array(
                    'service_name' => array('isValid' => 'Either service_name or adapter_name and table_name must be present'),
                    'adapter_name' => array('isValid' => 'Either service_name or adapter_name and table_name must be present'),
                    'table_name' => array('isValid' => 'Either service_name or adapter_name and table_name must be present')
                )
            ),
            // invalid service_name
            array(
                array('service_name' => '_'),
                array(
                    'service_name' => array('serviceName' => "'_' is not a valid service name")
                )
            ),
            // adapter without table
            array(
                array('adapter_name' => 'Foo'),
                array(
                    'table_name' => array('isEmpty' => "Value is required and can't be empty")
                )
            ),
            // table without adapter
            array(
                array('table_name' => 'Foo'),
                array(
                    'adapter_name' => array('isEmpty' => "Value is required and can't be empty")
                )
            ),
            // both present
            array(
                array('service_name' => 'Foo', 'adapter_name' => 'bar'),
                array(
                    'service_name' => array('isValid' => 'service_name cannot be present with adapter_name or table_name'),
                    'adapter_name' => array('isValid' => 'service_name cannot be present with adapter_name or table_name'),
                    'table_name' => array('isValid' => 'service_name cannot be present with adapter_name or table_name')
                )
            )
        );
    }
}
 