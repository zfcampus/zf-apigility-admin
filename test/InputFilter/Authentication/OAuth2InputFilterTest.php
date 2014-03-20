<?php

namespace ZFTest\Apigility\Admin\InputFilter\Authentication;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\InputFilter\Authentication\OAuth2InputFilter;

class OAuth2InputFilterTest extends TestCase
{
    /**
     * @dataProvider dataProviderIsValidTrue
     */
    public function testIsValidTrue($data)
    {
        $i = new OAuth2InputFilter;
        $i->setData($data);
        $this->assertTrue($i->isValid());
    }

    public function dataProviderIsValidTrue()
    {
        return array(
            // minimal
            array(
                array(
                    'dsn' => 'sqlite://:memory:',
                    'dsn_type' => 'PDO',
                    'route_match' => '/foo',
                )
            ),
            // full
            array(
                array(
                    'dsn' => 'sqlite://:memory:',
                    'dsn_type' => 'PDO',
                    'password' => 'foobar',
                    'route_match' => '/foo',
                    'username' => 'barfoo'
                )
            )
        );
    }

    /**
     * @dataProvider dataProviderIsValidFalse
     */
    public function testIsValidFalse($data, $messages)
    {
        $i = new OAuth2InputFilter;
        $i->setData($data);
        $this->assertFalse($i->isValid());
        $this->assertEquals($messages, $i->getMessages());
    }

    public function dataProviderIsValidFalse()
    {
        return array(
            // empty
            array(
                array(),
                array(
                    'dsn' => array('isEmpty' => 'Value is required and can\'t be empty'),
                    'dsn_type' => array('isEmpty' => 'Value is required and can\'t be empty'),
                    'route_match' => array('isEmpty' => 'Value is required and can\'t be empty'),
                )
            ),
            // null
            array(
                array(
                    'dsn' => '',
                    'dsn_type' => '',
                    'password' => '',
                    'route_match' => '',
                    'username' => ''
                ),
                array(
                    'dsn' => array('isEmpty' => 'Value is required and can\'t be empty'),
                    'dsn_type' => array('isEmpty' => 'Value is required and can\'t be empty'),
                    'route_match' => array('isEmpty' => 'Value is required and can\'t be empty'),
                )
            ),
        );
    }
}
 