<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\InputFilter;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\InputFilter\Factory;

class ModuleInputFilterTest extends TestCase
{
    public function getInputFilter()
    {
        $factory = new Factory();
        return $factory->createInputFilter([
            'type' => 'ZF\Apigility\Admin\InputFilter\ModuleInputFilter',
        ]);
    }

    public function dataProviderIsValid()
    {
        return [
            'singular-namespace' => [
                ['name' => 'Foo'],
            ],
            'underscore_namespace' => [
                ['name' => 'My_Status'],
            ],
        ];
    }

    public function dataProviderIsInvalid()
    {
        return [
            'missing-name' => [
                [],
                ['name'],
            ],
            'empty-name' => [
                ['name' => ''],
                ['name'],
            ],
            'underscore-only' => [
                ['name' => '_'],
                ['name'],
            ],
            'namespace-separator' => [
                ['name' => 'My\Status'],
                ['name'],
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
        $messageKeys = array_keys($messages);
        sort($expectedMessageKeys);
        sort($messageKeys);
        $this->assertEquals($expectedMessageKeys, $messageKeys);
    }
}
