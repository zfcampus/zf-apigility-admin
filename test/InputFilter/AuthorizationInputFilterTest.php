<?php

namespace ZFTest\Apigility\Admin\InputFilter;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\InputFilter\AuthorizationInputFilter;

class AuthorizationInputFilterTest extends TestCase
{
    /**
     * @dataProvider dataProviderIsValidTrue
     */
    public function testIsValidTrue($data)
    {
        $i = new AuthorizationInputFilter;
        $i->setData($data);
        $this->assertTrue($i->isValid());
    }

    public function dataProviderIsValidTrue()
    {
        return array(
            // empty
            array(
                array()
            ),
            // with values
            array(
                array(
                    'Foo\V1\Rest\Bar\Controller::__entity__' => array('POST' => true, 'GET' => false),
                    'Foo\V1\Rpc\Boom\Controller::boom' => array('GET' => true, 'DELETE' => false, 'PATCH' => true)
                )
            )
        );
    }

    /**
     * @dataProvider dataProviderIsValidFalse
     */
    public function testIsValidFalse($data, $messages)
    {
        $i = new AuthorizationInputFilter;
        $i->setData($data);
        $this->assertFalse($i->isValid());
        $this->assertEquals($messages, $i->getMessages());
    }

    public function dataProviderIsValidFalse()
    {
        return array(
            // invalid controller name
            array(
                array(
                    'Foo\V1\Rest\Bar\Controller' => array(),
                ),
                array(
                    'Foo\V1\Rest\Bar\Controller' => array('invalidClassName' => 'Class service name is invalid, must be serviceName::method')
                )
            ),
            // not an array for values
            array(
                array(
                    'Foo\V1\Rest\Bar\Controller::__entity__' => 'GET=true',
                ),
                array(
                    'Foo\V1\Rest\Bar\Controller::__entity__' => array('invalidHttpMethod' => 'Values for each controller must be an http method keyd array of true/false values')
                )
            ),
            // Invalid HTTP method
            array(
                array(
                    'Foo\V1\Rest\Bar\Controller::__entity__' => array('MYMETHOD' => true),
                ),
                array(
                    'Foo\V1\Rest\Bar\Controller::__entity__' => array('invalidHttpMethod' => 'Invalid header (MYMETHOD) provided.')
                )
            ),
        );
    }
}
