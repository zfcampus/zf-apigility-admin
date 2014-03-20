<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\InputFilter\RestService;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\InputFilter\Factory;
use ZF\Apigility\Admin\InputFilter\RestService\PostInputFilter;

class PostInputFilterTest extends TestCase
{
    public function getInputFilter()
    {
        $factory = new Factory();
        return $factory->createInputFilter(array(
            'type' => 'ZF\Apigility\Admin\InputFilter\RestService\PostInputFilter',
        ));
    }

    public function dataProviderIsValid()
    {
        return array(
            'code-connected' => array(array('service_name' => 'Foo')),
            'db-connected'   => array(array('adapter_name' => 'Status', 'table_name' => 'foo')),
        );
    }

    public function dataProviderIsInvalid()
    {
        return array(
            // no values
            'empty' => array(
                array(),
                array('service_name'),
            ),
            // invalid service_name
            'invalid-service-name' => array(
                array('service_name' => '_'),
                array('service_name'),
            ),
            // adapter without table
            'valid-adapter-missing-table' => array(
                array('adapter_name' => 'Foo'),
                array('table_name'),
            ),
            // table without adapter
            'missing-adapter-valid-table' => array(
                array('table_name' => 'Foo'),
                array('adapter_name'),
            ),
            // both present
            'conflict' => array(
                array('service_name' => 'Foo', 'adapter_name' => 'bar'),
                array('service_name'),
            )
        );
    }

    /**
     * @dataProvider dataProviderIsValid
     */
    public function testIsValidTrue($data)
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        $this->assertTrue($filter->isValid());
    }

    /**
     * @dataProvider dataProviderIsInvalid
     */
    public function testIsValidFalse($data, $expectedValidationKeys)
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        $this->assertFalse($filter->isValid());

        $messages = $filter->getMessages();
        $messages = array_keys($messages);
        sort($expectedValidationKeys);
        sort($messages);
        $this->assertEquals($expectedValidationKeys, $messages);
    }
}
