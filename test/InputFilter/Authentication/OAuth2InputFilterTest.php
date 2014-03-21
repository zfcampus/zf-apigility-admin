<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\InputFilter\Authentication;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\InputFilter\Factory;
use ZF\Apigility\Admin\InputFilter\Authentication\OAuth2InputFilter;

class OAuth2InputFilterTest extends TestCase
{
    public function getInputFilter()
    {
        $factory = new Factory;
        return $factory->createInputFilter(array(
            'type' => 'ZF\Apigility\Admin\InputFilter\Authentication\OAuth2InputFilter',
        ));
    }

    public function dataProviderIsValid()
    {
        return array(
            'minimal' => array(
                array(
                    'dsn' => 'sqlite://:memory:',
                    'dsn_type' => 'PDO',
                    'route_match' => '/foo',
                ),
            ),
            'full' => array(
                array(
                    'dsn' => 'sqlite://:memory:',
                    'dsn_type' => 'PDO',
                    'password' => 'foobar',
                    'route_match' => '/foo',
                    'username' => 'barfoo',
                ),
            ),
            'mongo-without-dsn' => array(
                array(
                    'dsn_type' => 'Mongo',
                    'database' => 'oauth2',
                    'route_match' => '/oauth2',
                ),
            ),
        );
    }

    public function dataProviderIsInvalid()
    {
        return array(
            'empty' => array(
                array(),
                array(
                    'dsn',
                    'dsn_type',
                    'route_match',
                ),
            ),
            'empty-values' => array(
                array(
                    'dsn' => '',
                    'dsn_type' => '',
                    'password' => '',
                    'route_match' => '',
                    'username' => '',
                ),
                array(
                    'dsn',
                    'dsn_type',
                    'route_match',
                ),
            ),
            'mongo-without-database' => array(
                array(
                    'dsn_type' => 'Mongo',
                    'route_match' => '/oauth2',
                ),
                array(
                    'database',
                ),
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
        $this->assertTrue($filter->isValid(), var_export($filter->getMessages(), 1));
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
