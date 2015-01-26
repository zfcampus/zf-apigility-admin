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
            'with-content-name' => array(
                array(
                    'content_name' => 'test',
                    'Zend\View\Model\ViewModel' => array('text/html', 'application/xhtml+xml'),
                )
            ),
        );
    }

    public function dataProviderIsInvalid()
    {
        return array(
            'missing-content-name' => array(
                array(
                    'Zend\View\Model\ViewModel' => array('text/html', 'application/xhtml+xml'),
                ),
                array(
                    'content_name' => array('No content_name was provided; must be present for new negotiators.'),
                ),
            ),
            'null-content-name' => array(
                array(
                    'content_name' => null,
                    'Zend\View\Model\ViewModel' => array('text/html', 'application/xhtml+xml'),
                ),
                array(
                    'content_name' => array('Content name provided is invalid; must be a string'),
                ),
            ),
            'bool-content-name' => array(
                array(
                    'content_name' => true,
                    'Zend\View\Model\ViewModel' => array('text/html', 'application/xhtml+xml'),
                ),
                array(
                    'content_name' => array('Content name provided is invalid; must be a string'),
                ),
            ),
            'int-content-name' => array(
                array(
                    'content_name' => 1,
                    'Zend\View\Model\ViewModel' => array('text/html', 'application/xhtml+xml'),
                ),
                array(
                    'content_name' => array('Content name provided is invalid; must be a string'),
                ),
            ),
            'float-content-name' => array(
                array(
                    'content_name' => 1.1,
                    'Zend\View\Model\ViewModel' => array('text/html', 'application/xhtml+xml'),
                ),
                array(
                    'content_name' => array('Content name provided is invalid; must be a string'),
                ),
            ),
            'array-content-name' => array(
                array(
                    'content_name' => array('content_name'),
                    'Zend\View\Model\ViewModel' => array('text/html', 'application/xhtml+xml'),
                ),
                array(
                    'content_name' => array('Content name provided is invalid; must be a string'),
                ),
            ),
            'object-content-name' => array(
                array(
                    'content_name' => (object) array('content_name'),
                    'Zend\View\Model\ViewModel' => array('text/html', 'application/xhtml+xml'),
                ),
                array(
                    'content_name' => array('Content name provided is invalid; must be a string'),
                ),
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
