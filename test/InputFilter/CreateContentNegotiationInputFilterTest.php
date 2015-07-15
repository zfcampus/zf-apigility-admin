<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\InputFilter;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\InputFilter\CreateContentNegotiationInputFilter;

class CreateContentNegotiationInputFilterTest extends TestCase
{
    public function dataProviderIsValid()
    {
        return [
            'content-name-only' => [
                [
                    'content_name' => 'test',
                ],
            ],
            'content-name-and-selectors' => [
                [
                    'content_name' => 'test',
                    'selectors' => [
                        'Zend\View\Model\ViewModel' => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
            ],
        ];
    }

    public function dataProviderIsInvalid()
    {
        return [
            'missing-content-name' => [
                [
                    'selectors' => [
                        'Zend\View\Model\ViewModel' => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
                [
                    'content_name' => [
                        'isEmpty' => 'Value is required and can\'t be empty'
                    ],
                ],
            ],
            'null-content-name' => [
                [
                    'content_name' => null,
                    'selectors' => [
                        'Zend\View\Model\ViewModel' => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
                ['content_name' => [
                    'isEmpty' => 'Value is required and can\'t be empty',
                ]],
            ],
            'bool-content-name' => [
                [
                    'content_name' => true,
                    'selectors' => [
                        'Zend\View\Model\ViewModel' => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
                ['content_name' => [
                    'invalidType' => 'Value must be a string; received boolean',
                ]],
            ],
            'int-content-name' => [
                [
                    'content_name' => 1,
                    'selectors' => [
                        'Zend\View\Model\ViewModel' => ['text/html', 'application/xhtml+xml'],
                    ]
                ],
                ['content_name' => [
                    'invalidType' => 'Value must be a string; received integer',
                ]],
            ],
            'float-content-name' => [
                [
                    'content_name' => 1.1,
                    'selectors' => [
                        'Zend\View\Model\ViewModel' => ['text/html', 'application/xhtml+xml'],
                    ]
                ],
                ['content_name' => [
                    'invalidType' => 'Value must be a string; received double',
                ]],
            ],
            'array-content-name' => [
                [
                    'content_name' => ['content_name'],
                    'selectors' => [
                        'Zend\View\Model\ViewModel' => ['text/html', 'application/xhtml+xml'],
                    ]
                ],
                ['content_name' => [
                    'invalidType' => 'Value must be a string; received array',
                ]],
            ],
            'object-content-name' => [
                [
                    'content_name' => (object) ['content_name'],
                    'selectors' => [
                        'Zend\View\Model\ViewModel' => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
                ['content_name' => [
                    'invalidType' => 'Value must be a string; received stdClass',
                ]],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderIsValid
     */
    public function testIsValid($data)
    {
        $filter = new CreateContentNegotiationInputFilter;
        $filter->setData($data);
        $this->assertTrue($filter->isValid(), var_export($filter->getMessages(), 1));
    }

    /**
     * @dataProvider dataProviderIsInvalid
     */
    public function testIsInvalid($data, $messages)
    {
        $filter = new CreateContentNegotiationInputFilter;
        $filter->setData($data);
        $this->assertFalse($filter->isValid());
        $this->assertEquals($messages, $filter->getMessages());
    }
}
