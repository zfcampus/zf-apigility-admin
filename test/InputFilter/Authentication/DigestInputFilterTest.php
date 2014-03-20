<?php

namespace ZFTest\Apigility\Admin\InputFilter\Authentication;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\InputFilter\Authentication\DigestInputFilter;

class DigestInputFilterTest extends TestCase
{
    /**
     * @dataProvider dataProviderIsValidTrue
     */
    public function testIsValidTrue($data)
    {
        $i = new DigestInputFilter;
        $i->setData($data);
        $this->assertTrue($i->isValid());
    }

    public function dataProviderIsValidTrue()
    {
        return array(
            array(
                array(
                    'accept_schemes' => array('digest'),
                    'digest_domains' => 'foo.local',
                    'realm' => 'My Realm',
                    'htdigest' => 'tmp/file.htpasswd',
                    'nonce_timeout' => 3600
                )
            ),
        );
    }

    /**
     * @dataProvider dataProviderIsValidFalse
     */
    public function testIsValidFalse($data, $messages)
    {
        $i = new DigestInputFilter;
        $i->setData($data);
        $this->assertFalse($i->isValid());
        $this->assertEquals($messages, $i->getMessages());
    }

    public function dataProviderIsValidFalse()
    {
        return array(
            // nothing sent
            array(
                array(),
                array(
                    'accept_schemes' => array('isEmpty' => 'Value is required and can\'t be empty'),
                    'digest_domains' => array('isEmpty' => 'Value is required and can\'t be empty'),
                    'realm' => array('isEmpty' => 'Value is required and can\'t be empty'),
                    'htdigest' => array('isEmpty' => 'Value is required and can\'t be empty'),
                    'nonce_timeout' => array('isEmpty' => 'Value is required and can\'t be empty'),
                )
            ),
            // noonce is digit
            array(
                array(
                   'accept_schemes' => array('digest'),
                    'digest_domains' => 'foo.local',
                    'realm' => 'My Realm',
                    'htdigest' => 'tmp/file.htpasswd',
                    'nonce_timeout' => 'foo'
                ),
                array(
                    'nonce_timeout' => array('notDigits' => 'The input must contain only digits'),
                )
            )
        );
    }
}
 