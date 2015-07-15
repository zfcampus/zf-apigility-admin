<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\InputFilter;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\InputFilter\ContentNegotiationInputFilter;

class ContentNegotiationInputFilterTest extends TestCase
{
    public function dataProviderIsValid()
    {
        return [
            'valid' => [['selectors' =>
                [
                    'Zend\View\Model\ViewModel' => ['text/html', 'application/xhtml+xml'],
                ],
            ]],
        ];
    }

    public function dataProviderIsInvalid()
    {
        return [
            'class-does-not-exist' => [
                ['selectors' => [
                    'Zend\View\Model\ViewMode' => ['text/html', 'application/xhtml+xml'],
                ]],
                ['selectors' => [
                    'classNotFound' => 'Class name (Zend\View\Model\ViewMode) does not exist',
                ]],
            ],
            'class-is-not-view-model' => [
                ['selectors' => [
                    __CLASS__ => ['text/html', 'application/xhtml+xml'],
                ]],
                ['selectors' => [
                    'invalidViewModel' => 'Class name (' . __CLASS__ . ') is invalid;'
                    . ' must be a valid Zend\View\Model\ModelInterface instance',
                ]],
            ],
            'media-types-not-array' => [
                ['selectors' => [
                    'Zend\View\Model\ViewModel' => 'foo',
                ]],
                ['selectors' => [
                    'invalidMediaTypes' => 'Values for the media-types must be provided as an indexed array',
                ]],
            ],
            'invalid-media-type' => [
                ['selectors' => [
                    'Zend\View\Model\ViewModel' => ['texthtml', 'application/xhtml+xml'],
                ]],
                ['selectors' => [
                    'invalidMediaType' => 'Invalid media type (texthtml) provided',
                ]],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderIsValid
     */
    public function testIsValid($data)
    {
        $filter = new ContentNegotiationInputFilter;
        $filter->setData($data);
        $this->assertTrue($filter->isValid(), var_export($filter->getMessages(), 1));
    }

    /**
     * @dataProvider dataProviderIsInvalid
     */
    public function testIsInvalid($data, $messages)
    {
        $filter = new ContentNegotiationInputFilter;
        $filter->setData($data);
        $input = $filter->get('selectors');
        $this->assertFalse($filter->isValid());
        $this->assertEquals($messages, $filter->getMessages());
    }
}
