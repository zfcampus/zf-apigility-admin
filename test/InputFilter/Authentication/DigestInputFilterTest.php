<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\InputFilter\Authentication;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\InputFilter\Factory;

class DigestInputFilterTest extends TestCase
{
    public function setUp()
    {
        $this->htdigest = sys_get_temp_dir() . '/' . uniqid() . '.htdigest';
        touch($this->htdigest);
    }

    public function tearDown()
    {
        unlink($this->htdigest);
    }

    public function getInputFilter()
    {
        $factory = new Factory();
        return $factory->createInputFilter(array(
            'type' => 'ZF\Apigility\Admin\InputFilter\Authentication\DigestInputFilter',
        ));
    }

    public function dataProviderIsValid()
    {
        return array(
            'valid' => array(
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

    public function dataProviderIsInvalid()
    {
        return array(
            'no-data' => array(
                array(),
                array(
                    'accept_schemes',
                    'digest_domains',
                    'realm',
                    'htdigest',
                    'nonce_timeout',
                ),
            ),
            'nonce-is-not-a-digit' => array(
                array(
                    'accept_schemes' => array('digest'),
                    'digest_domains' => 'foo.local',
                    'realm' => 'My Realm',
                    'htdigest' => '%HTDIGEST%',
                    'nonce_timeout' => 'foo',
                ),
                array(
                    'nonce_timeout',
                ),
            ),
            'invalid-htdigest' => array(
                array(
                    'accept_schemes' => array('digest'),
                    'digest_domains' => 'foo.local',
                    'realm' => 'My Realm',
                    'htdigest' => '/foo/bar/baz/bat.htpasswd',
                    'nonce_timeout' => 3600,
                ),
                array(
                    'htdigest',
                ),
            ),
        );
    }

    /**
     * @dataProvider dataProviderIsValid
     */
    public function testIsValid($data)
    {
        $data['htdigest'] = $this->htdigest;
        $filter = $this->getInputFilter();
        $filter->setData($data);
        $this->assertTrue($filter->isValid(), var_export($filter->getMessages(), 1));
    }

    /**
     * @dataProvider dataProviderIsInvalid
     */
    public function testIsInvalid($data, $expectedMessageKeys)
    {
        if (isset($data['htdigest'])) {
            $data['htdigest'] = str_replace('%HTDIGEST%', $this->htdigest, $data['htdigest']);
        }

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
