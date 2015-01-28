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
        return array(
            'content-name-only' => array(
                array(
                    'content_name' => 'test',
                ),
            ),
            'content-name-and-selectors' => array(
                array(
                    'content_name' => 'test',
                    'selectors' => array(
                        'Zend\View\Model\ViewModel' => array('text/html', 'application/xhtml+xml'),
                    ),
                ),
            ),
        );
    }

    public function dataProviderIsInvalid()
    {
        return array(
            'missing-content-name' => array(
                array(
                    'selectors' => array(
                        'Zend\View\Model\ViewModel' => array('text/html', 'application/xhtml+xml'),
                    ),
                ),
                array(
                    'content_name' => array(
                        'isEmpty' => 'Value is required and can\'t be empty'
                    ),
                ),
            ),
            'null-content-name' => array(
                array(
                    'content_name' => null,
                    'selectors' => array(
                        'Zend\View\Model\ViewModel' => array('text/html', 'application/xhtml+xml'),
                    ),
                ),
                array('content_name' => array(
                    'isEmpty' => 'Value is required and can\'t be empty',
                )),
            ),
            'bool-content-name' => array(
                array(
                    'content_name' => true,
                    'selectors' => array(
                        'Zend\View\Model\ViewModel' => array('text/html', 'application/xhtml+xml'),
                    ),
                ),
                array('content_name' => array(
                    'invalidType' => 'Value must be a string; received boolean',
                )),
            ),
            'int-content-name' => array(
                array(
                    'content_name' => 1,
                    'selectors' => array(
                        'Zend\View\Model\ViewModel' => array('text/html', 'application/xhtml+xml'),
                    )
                ),
                array('content_name' => array(
                    'invalidType' => 'Value must be a string; received integer',
                )),
            ),
            'float-content-name' => array(
                array(
                    'content_name' => 1.1,
                    'selectors' => array(
                        'Zend\View\Model\ViewModel' => array('text/html', 'application/xhtml+xml'),
                    )
                ),
                array('content_name' => array(
                    'invalidType' => 'Value must be a string; received double',
                )),
            ),
            'array-content-name' => array(
                array(
                    'content_name' => array('content_name'),
                    'selectors' => array(
                        'Zend\View\Model\ViewModel' => array('text/html', 'application/xhtml+xml'),
                    )
                ),
                array('content_name' => array(
                    'invalidType' => 'Value must be a string; received array',
                )),
            ),
            'object-content-name' => array(
                array(
                    'content_name' => (object) array('content_name'),
                    'selectors' => array(
                        'Zend\View\Model\ViewModel' => array('text/html', 'application/xhtml+xml'),
                    ),
                ),
                array('content_name' => array(
                    'invalidType' => 'Value must be a string; received stdClass',
                )),
            ),
        );
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
