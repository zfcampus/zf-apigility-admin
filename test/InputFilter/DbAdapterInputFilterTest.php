<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\InputFilter;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\InputFilter\Factory;

class DbAdapterInputFilterTest extends TestCase
{
    public function getInputFilter()
    {
        $factory = new Factory();
        return $factory->createInputFilter([
            'type' => 'ZF\Apigility\Admin\InputFilter\DbAdapterInputFilter',
        ]);
    }

    public function dataProviderIsValid()
    {
        return [
            'valid' => [
                [
                    'adapter_name' => 'Db\Status',
                    'database' => '/path/to/foobar',
                    'driver' => 'pdo_sqlite',
                ],
            ],
        ];
    }

    public function dataProviderIsInvalid()
    {
        return [
            'missing-adapter-name' => [
                [
                    'database' => '/path/to/foobar',
                    'driver' => 'pdo_sqlite',
                ],
                ['adapter_name'],
            ],
            'missing-database' => [
                [
                    'adapter_name' => 'Db\Status',
                    'driver' => 'pdo_sqlite',
                ],
                ['database'],
            ],
            'missing-driver' => [
                [
                    'adapter_name' => 'Db\Status',
                    'database' => '/path/to/foobar',
                ],
                ['driver'],
            ],
        ];
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
