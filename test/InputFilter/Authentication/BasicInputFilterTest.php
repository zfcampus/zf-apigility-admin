<?php

namespace ZFTest\Apigility\Admin\InputFilter\Authentication;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\InputFilter\Authentication\BasicInputFilter;

class BasicInputFilterTest extends TestCase
{
    /**
     * @dataProvider dataProviderIsValidTrue
     */
    public function testIsValidTrue($data)
    {
        $i = new BasicInputFilter;
        $i->setData($data);
        $this->assertTrue($i->isValid());
    }

    public function dataProviderIsValidTrue()
    {
        return array(
            array(
                array('accept_schemes' => array('basic'), 'realm' => 'My Realm', 'htpasswd' => 'tmp/file.htpasswd')
            ),
            array(
                array('accept_schemes' => array('digest', 'basic'), 'realm' => 'My Realm', 'htpasswd' => 'file.htpasswd')
            ),
        );
    }

    /**
     * @dataProvider dataProviderIsValidFalse
     */
    public function testIsValidFalse($data, $messages)
    {
        $i = new BasicInputFilter;
        $i->setData($data);
        $this->assertFalse($i->isValid());
        $this->assertEquals($messages, $i->getMessages());
    }

    public function dataProviderIsValidFalse()
    {
        return array(
            // no data
            array(
                array(),
                array(
                    'accept_schemes' => array('isEmpty' => 'Value is required and can\'t be empty'),
                    'realm' => array('isEmpty' => 'Value is required and can\'t be empty'),
                    'htpasswd' => array('isEmpty' => 'Value is required and can\'t be empty')
                )
            ),
            // empty data
            array(
                array('accept_schemes' => '', 'realm' => '', 'htpasswd' => ''),
                array(
                    'accept_schemes' => array('isEmpty' => 'Value is required and can\'t be empty'),
                    'realm' => array('isEmpty' => 'Value is required and can\'t be empty'),
                    'htpasswd' => array('isEmpty' => 'Value is required and can\'t be empty')
                )
            ),
        );
    }
}
 