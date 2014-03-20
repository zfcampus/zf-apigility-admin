<?php

namespace ZFTest\Apigility\Admin\InputFilter;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\InputFilter\InputFilterPluginManager;
use ZF\Apigility\Admin\InputFilter\InputFilterInputFilter;

class InputFilterInputFilterTest extends TestCase
{
    protected $inputFilterInputFilter;

    public function setup()
    {
        $this->inputFilterInputFilter = new InputFilterInputFilter(new InputFilterPluginManager());
    }

    /**
     * @dataProvider dataProviderIsValidTrue
     */
    public function testIsValidTrue($data)
    {
        $this->inputFilterInputFilter->setData($data);
        $this->assertTrue($this->inputFilterInputFilter->isValid());
    }

    public function dataProviderIsValidTrue()
    {
        //[{"name":"one","required":true,"filters":[{"name":"Zend\\Filter\\Boolea","options":{"casting":false}}],"validators":[],"allow_empty":false,"continue_if_empty":false}]
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

    /**
     * @dataProvider dataProviderIsValidFalse
     */
    public function testIsValidFalse($data, $messages)
    {
        $this->inputFilterInputFilter->setData($data);
        $this->assertFalse($this->inputFilterInputFilter->isValid());
        $this->assertEquals($messages, $this->inputFilterInputFilter->getMessages());
    }

    public function dataProviderIsValidFalse()
    {
        return array(
            array(
                array('foobar' => 'baz'),
                array('inputFilter' => array('isValid' => 'Zend\InputFilter\Factory::createInput expects an array or Traversable; received "string"'))
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
                array('inputFilter' => array('isValid' => 'Zend\Filter\FilterPluginManager::get was unable to fetch or create an instance for Zend\Filter\Bool'))
            )
        );
    }
}
