<?php

namespace ZFTest\Apigility\Admin\InputFilter\RpcService;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\InputFilter\RpcService\PatchInputFilter;

class PatchInputFilterTest extends TestCase
{
    /**
     * @dataProvider dataProviderIsValidTrue
     */
    public function testIsValidTrue($data)
    {
        $i = new PatchInputFilter;
        $i->setData($data);
        $this->assertTrue($i->isValid());
    }

    public function dataProviderIsValidTrue()
    {
        return array(
            array(
                array(
                    'service_name' => 'Foo',
                    'route_match' => '/foo',
                    'module' => 'FooBar',
                    'controller_service_name' => 'FooBar\V1\Rpc\Foo\Controller',
                    'controller_class' => 'FooBar\V1\Rpc\Foo\FooController',
                    'route_name' => 'foobar.rest.foo',
                    'accept_whitelist' => array('application/vnd.foo.v1+json', 'application/hal+json', 'application/json'),
                    'content_type_whitelist' => array('application/vnd.foo.v1+json', 'application/json'),
                    'selector' => 'HalJson',
                    'http_methods' => array('GET', 'POST', 'PATCH')
                )
            )
        );
    }

    /**
     * @dataProvider dataProviderIsValidFalse
     */
    public function testIsValidFalse($data, $messages)
    {
        $i = new PatchInputFilter;
        $i->setData($data);
        $this->assertFalse($i->isValid());
        $this->assertEquals($messages, $i->getMessages());
    }

    public function dataProviderIsValidFalse()
    {
        return array(
            array(
                array(
                    'route_match' => '/foo',
                    'module' => 'FooBar',
                    'controller_service_name' => 'FooBar\V1\Rpc\Foo\Controller',
                    'controller_class' => 'FooBar\V1\Rpc\Foo\FooController',
                    'route_name' => 'foobar.rest.foo',
                    'accept_whitelist' => array('application/vnd.foo.v1+json', 'application/hal+json', 'application/json'),
                    'content_type_whitelist' => array('application/vnd.foo.v1+json', 'application/json'),
                    'selector' => 'HalJson',
                    'http_methods' => array('GET', 'POST', 'PATCH')
                ),
                array('service_name' => array('isEmpty' => "Value is required and can't be empty"))
            )
        );
    }
}
