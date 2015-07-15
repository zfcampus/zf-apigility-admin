<?php

namespace ZFTest\Apigility\Admin\InputFilter\RpcService;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\InputFilter\Factory;

class PostInputFilterTest extends TestCase
{
    public function getInputFilter()
    {
        $factory = new Factory();
        return $factory->createInputFilter([
            'type' => 'ZF\Apigility\Admin\InputFilter\RpcService\PostInputFilter',
        ]);
    }

    public function dataProviderIsValid()
    {
        return [
            'singular-service-name' => [
                ['service_name' => 'Foo', 'route_match' => '/bar'],
            ],
            'compound-service-name' => [
                ['service_name' => 'Foo_Bar', 'route_match' => '/bar'],
            ],
        ];
    }

    public function dataProviderIsInvalid()
    {
        return [
            'empty' => [
                [],
                ['service_name', 'route_match'],
            ],
            'missing-service-name' => [
                ['route_match' => '/bar'],
                ['service_name'],
            ],
            'missing-route-match' => [
                ['service_name' => 'Foo_Bar'],
                ['route_match'],
            ],
            'namespaced-service-name' => [
                ['service_name' => 'Foo\Bar', 'route_match' => '/bar'],
                ['service_name'],
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
