<?php

namespace ZFTest\Apigility\Admin\InputFilter\RpcService;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\InputFilter\Factory;

class PatchInputFilterTest extends TestCase
{
    public function getInputFilter()
    {
        $factory = new Factory();
        return $factory->createInputFilter([
            'type' => 'ZF\Apigility\Admin\InputFilter\RpcService\PatchInputFilter',
        ]);
    }

    public function dataProviderIsValid()
    {
        return [
            [
                [
                    'service_name' => 'Foo',
                    'route_match' => '/foo',
                    'controller_class' => 'FooBar\V1\Rpc\Foo\FooController',
                    'accept_whitelist' => [
                        'application/vnd.foo.v1+json',
                        'application/hal+json',
                        'application/json'
                    ],
                    'content_type_whitelist' => [
                        'application/vnd.foo.v1+json',
                        'application/json'
                    ],
                    'selector' => 'HalJson',
                    'http_methods' => ['GET', 'POST', 'PATCH'],
                ]
            ]
        ];
    }

    public function dataProviderIsInvalid()
    {
        return [
            'missing-service-name' => [
                [
                    'route_match' => '/foo',
                    'controller_class' => 'FooBar\V1\Rpc\Foo\FooController',
                    'accept_whitelist' => [
                        'application/vnd.foo.v1+json',
                        'application/hal+json',
                        'application/json'
                    ],
                    'content_type_whitelist' => [
                        'application/vnd.foo.v1+json',
                        'application/json'
                    ],
                    'selector' => 'HalJson',
                    'http_methods' => ['GET', 'POST', 'PATCH'],
                ],
                ['service_name'],
            ],
            'null-values' => [
                [
                    'service_name' => 'Foo',
                    'route_match' => null,
                    'controller_class' => null,
                    'accept_whitelist' => [
                        'application/vnd.foo.v1+json',
                        'application/hal+json', 'application/json'
                    ],
                    'content_type_whitelist' => [
                        'application/vnd.foo.v1+json',
                        'application/json'
                    ],
                    'selector' => null,
                    'http_methods' => ['GET', 'POST', 'PATCH']
                ],
                ['route_match', 'controller_class'],
            ],
        ];
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
