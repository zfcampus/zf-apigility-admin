<?php

namespace ZFTest\Apigility\Admin\InputFilter;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\InputFilter\DbAdapterInputFilter;

class DbAdapterInputFilterTest extends TestCase
{
    /**
     * @dataProvider dataProviderIsValidTrue
     */
    public function testIsValidTrue($data)
    {
        $i = new DbAdapterInputFilter;
        $i->setData($data);
        $this->assertTrue($i->isValid());
    }

    public function dataProviderIsValidTrue()
    {
        return array(
            array(
                array('adapter_name' => 'Db\Status', 'database' => '/path/to/foobar', 'driver' => 'pdo_sqlite')
            )
        );
    }

    /**
     * @dataProvider dataProviderIsValidFalse
     */
    public function testIsValidFalse($data, $messages)
    {
        $i = new DbAdapterInputFilter;
        $i->setData($data);
        $this->assertFalse($i->isValid());
        $this->assertEquals($messages, $i->getMessages());
    }

    public function dataProviderIsValidFalse()
    {
        return array(
            // adapter_name must be present
            array(
                array('database' => '/path/to/foobar', 'driver' => 'pdo_sqlite'),
                array('adapter_name' => array('isEmpty' => 'Value is required and can\'t be empty'))
            ),
            // database must be present
            array(
                array('adapter_name' => 'Db\Status', 'driver' => 'pdo_sqlite'),
                array('database' => array('isEmpty' => 'Value is required and can\'t be empty'))
            ),
            // driver must be present
            array(
                array('adapter_name' => 'Db\Status', 'database' => '/path/to/foobar'),
                array('driver' => array('isEmpty' => 'Value is required and can\'t be empty'))
            ),
        );
    }
}
 