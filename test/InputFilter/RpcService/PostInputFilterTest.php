<?php

namespace ZFTest\Apigility\Admin\InputFilter\RpcService;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\InputFilter\RpcService\PostInputFilter;

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
                array('service_name' => 'Foo', 'route_match' => '/bar')
            ),
            array(
                array('service_name' => 'Foo_Bar', 'route_match' => '/bar')
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
            // nothing
            array(
                array(),
                array(
                    'service_name' => array('isEmpty' => "Value is required and can't be empty"),
                    'route_match' => array('isEmpty' => "Value is required and can't be empty")
                )
            ),
            // missing service_name
            array(
                array('route_match' => '/bar'),
                array('service_name' => array('isEmpty' => "Value is required and can't be empty"))
            ),
            // missing route_match
            array(
                array('service_name' => 'Foo_Bar'),
                array('route_match' => array('isEmpty' => "Value is required and can't be empty"))
            ),
            array(
                array('service_name' => 'Foo\Bar', 'route_match' => '/bar'),
                array('service_name' => array('serviceName' => "'Foo\\Bar' is not a valid service name"))
            ),

        );
    }
}
