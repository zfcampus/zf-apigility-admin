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
        return array(
            array(
                array(
                    array(
                        'name' => 'myfilter',
                        'required' => true,
                        'filters' => array(
                            array(
                                'name' => 'Zend\Filter\Boolean',
                                'options' => array('casting' => false),
                            )
                        ),
                        'validators' => array(),
                        'allow_empty' => true,
                        'continue_if_empty' => false,
                    )
                )
            )
        );
    }

    public function dataProviderIsInvalid()
    {
        return array(
            array(
                array('foobar' => 'baz'),
                array(
                    'inputFilter' => 'Zend\InputFilter\Factory::createInput expects'
                    . ' an array or Traversable; received "string"',
                ),
            ),
            array(
                array(
                    array(
                        'name' => 'myfilter',
                        'required' => true,
                        'filters' => array(
                            array(
                                'name' => 'Zend\Filter\Bool',
                                'options' => array('casting' => false),
                            )
                        ),
                        'validators' => array(),
                        'allow_empty' => true,
                        'continue_if_empty' => false,
                    )
                ),
                array(
                    'inputFilter' => 'Zend\Filter\FilterPluginManager::get was unable'
                    . ' to fetch or create an instance for Zend\Filter\Bool'
                ),
            ),
        );
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
