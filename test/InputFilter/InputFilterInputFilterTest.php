<?php

namespace ZFTest\Apigility\Admin\InputFilter;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\InputFilter\Factory;
use ZF\Apigility\Admin\InputFilter\InputFilterInputFilter;

class InputFilterInputFilterTest extends TestCase
{
    public function setup()
    {
        $this->inputFilterInputFilter = new InputFilterInputFilter(new Factory());
    }

    public function dataProviderIsValid()
    {
        return [
            [
                [
                    [
                        'name' => 'myfilter',
                        'required' => true,
                        'filters' => [
                            [
                                'name' => 'Zend\Filter\Boolean',
                                'options' => ['casting' => false],
                            ]
                        ],
                        'validators' => [],
                        'allow_empty' => true,
                        'continue_if_empty' => false,
                    ]
                ]
            ]
        ];
    }

    public function dataProviderIsInvalid()
    {
        return [
            [
                ['foobar' => 'baz'],
                [
                    'inputFilter' => 'Zend\InputFilter\Factory::createInput expects'
                    . ' an array or Traversable; received "string"',
                ],
            ],
            [
                [
                    [
                        'name' => 'myfilter',
                        'required' => true,
                        'filters' => [
                            [
                                'name' => 'Zend\Filter\Bool',
                                'options' => ['casting' => false],
                            ]
                        ],
                        'validators' => [],
                        'allow_empty' => true,
                        'continue_if_empty' => false,
                    ]
                ],
                [
                    'inputFilter' => 'Zend\Filter\FilterPluginManager::get was unable'
                    . ' to fetch or create an instance for Zend\Filter\Bool'
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderIsValid
     */
    public function testIsValid($data)
    {
        $this->inputFilterInputFilter->setData($data);
        $this->assertTrue($this->inputFilterInputFilter->isValid());
    }

    /**
     * @dataProvider dataProviderIsInvalid
     */
    public function testIsInvalid($data, $messages)
    {
        $this->inputFilterInputFilter->setData($data);
        $this->assertFalse($this->inputFilterInputFilter->isValid());
        $this->assertEquals($messages, $this->inputFilterInputFilter->getMessages());
    }
}
