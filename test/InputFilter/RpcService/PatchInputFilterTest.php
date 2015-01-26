<?php

namespace ZFTest\Apigility\Admin\InputFilter\RpcService;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\InputFilter\Factory;

class PatchInputFilterTest extends TestCase
{
    public function getInputFilter()
    {
        $factory = new Factory();
        return $factory->createInputFilter(array(
            'type' => 'ZF\Apigility\Admin\InputFilter\RpcService\PatchInputFilter',
        ));
    }

    public function dataProviderIsValid()
    {
        return array(
            array(
                array(
                    'service_name' => 'Foo',
                    'route_match' => '/foo',
                    'controller_class' => 'FooBar\V1\Rpc\Foo\FooController',
                    'accept_whitelist' => array(
                        'application/vnd.foo.v1+json',
                        'application/hal+json',
                        'application/json'
                    ),
                    'content_type_whitelist' => array(
                        'application/vnd.foo.v1+json',
                        'application/json'
                    ),
                    'selector' => 'HalJson',
                    'http_methods' => array('GET', 'POST', 'PATCH'),
                )
            )
        );
    }

    public function dataProviderIsInvalid()
    {
        return array(
            'missing-service-name' => array(
                array(
                    'route_match' => '/foo',
                    'controller_class' => 'FooBar\V1\Rpc\Foo\FooController',
                    'accept_whitelist' => array(
                        'application/vnd.foo.v1+json',
                        'application/hal+json',
                        'application/json'
                    ),
                    'content_type_whitelist' => array(
                        'application/vnd.foo.v1+json',
                        'application/json'
                    ),
                    'selector' => 'HalJson',
                    'http_methods' => array('GET', 'POST', 'PATCH'),
                ),
                array('service_name'),
            ),
            'null-values' => array(
                array(
                    'service_name' => 'Foo',
                    'route_match' => null,
                    'controller_class' => null,
                    'accept_whitelist' => array(
                        'application/vnd.foo.v1+json',
                        'application/hal+json', 'application/json'
                    ),
                    'content_type_whitelist' => array(
                        'application/vnd.foo.v1+json',
                        'application/json'
                    ),
                    'selector' => null,
                    'http_methods' => array('GET', 'POST', 'PATCH')
                ),
                array('route_match', 'controller_class'),
            ),
        );
    }

    /**
     * @dataProvider dataProviderIsValid
     */
    public function testIsValid($data)
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        $this->assertTrue($filter->isValid());
    }

    /**
     * @dataProvider dataProviderIsInvalid
     */
    public function testIsInvalid($data, $expectedMessageKeys)
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        $this->assertFalse($filter->isValid());

        $messages = $filter->getMessages();
        $messages = array_keys($messages);
        sort($expectedMessageKeys);
        sort($messages);
        $this->assertEquals($expectedMessageKeys, $messages);
    }
}
