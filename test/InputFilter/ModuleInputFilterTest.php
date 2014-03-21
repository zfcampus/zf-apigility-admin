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
        return $factory->createInputFilter(array(
            'type' => 'ZF\Apigility\Admin\InputFilter\ModuleInputFilter',
        ));
    }

    public function dataProviderIsValid()
    {
        return array(
            'singular-namespace' => array(
                array('name' => 'Foo'),
            ),
            'underscore_namespace' => array(
                array('name' => 'My_Status'),
            ),
        );
    }

    public function dataProviderIsInvalid()
    {
        return array(
            'missing-name' => array(
                array(),
                array('name'),
            ),
            'empty-name' => array(
                array('name' => ''),
                array('name'),
            ),
            'underscore-only' => array(
                array('name' => '_'),
                array('name'),
            ),
            'namespace-separator' => array(
                array('name' => 'My\Status'),
                array('name'),
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
        $messageKeys = array_keys($messages);
        sort($expectedMessageKeys);
        sort($messageKeys);
        $this->assertEquals($expectedMessageKeys, $messageKeys);
    }
}
