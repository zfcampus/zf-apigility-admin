<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\InputFilter;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\InputFilter\Factory;

class VersionInputFilterTest extends TestCase
{
    public function getInputFilter()
    {
        $factory = new Factory();
        return $factory->createInputFilter(array(
            'type' => 'ZF\Apigility\Admin\InputFilter\VersionInputFilter',
        ));
    }

    public function dataProviderIsValid()
    {
        return array(
            'valid' => array(
                array(
                    'module' => 'foo',
                    'version' => 5,
                ),
            ),
            'version-with-alphas' => array(
                array(
                    'module' => 'foo',
                    'version' => 'alpha',
                ),
            ),
            'version-with-mixed' => array(
                array(
                    'module' => 'foo',
                    'version' => 'alpha_1',
                ),
            ),
        );
    }

    public function dataProviderIsInvalid()
    {
        return array(
            'empty' => array(
                array(),
                array('module', 'version'),
            ),
            'missing-module' => array(
                array('version' => 'foo'),
                array('module'),
            ),
            'missing-version' => array(
                array('module' => 'foo'),
                array('version'),
            ),
            'version-with-spaces' => array(
                array('module' => 'foo', 'version' => 'foo bar'),
                array('version'),
            ),
            'version-with-dashes' => array(
                array('module' => 'foo', 'version' => 'foo-bar'),
                array('version'),
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
