<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\InputFilter\Authentication;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\InputFilter\Factory;

class BasicInputFilterTest extends TestCase
{
    public function setUp()
    {
        $this->htpasswd = sys_get_temp_dir() . '/' . uniqid() . '.htpasswd';
        touch($this->htpasswd);
    }

    public function tearDown()
    {
        unlink($this->htpasswd);
    }

    public function getInputFilter()
    {
        $factory = new Factory;
        return $factory->createInputFilter([
            'type' => 'ZF\Apigility\Admin\InputFilter\Authentication\BasicInputFilter',
        ]);
    }

    public function dataProviderIsValid()
    {
        return [
            'basic-only' => [
                ['accept_schemes' => ['basic'], 'realm' => 'My Realm']
            ],
            'basic-and-digest' => [
                ['accept_schemes' => ['digest', 'basic'], 'realm' => 'My Realm']
            ],
        ];
    }

    public function dataProviderIsInvalid()
    {
        return [
            'empty' => [
                [],
                [
                    'accept_schemes',
                    'realm',
                    'htpasswd',
                ],
            ],
            'empty-data' => [
                ['accept_schemes' => '', 'realm' => '', 'htpasswd' => ''],
                [
                    'accept_schemes',
                    'realm',
                    'htpasswd',
                ],
            ],
            'invalid-htpasswd' => [
                ['accept_schemes' => ['basic'], 'realm' => 'api', 'htpasswd' => '/foo/bar/baz/bat.htpasswd'],
                [
                    'htpasswd',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderIsValid
     */
    public function testIsValid($data)
    {
        $data['htpasswd'] = $this->htpasswd;
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
