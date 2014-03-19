<?php

namespace ZFTest\Apigility\Admin\InputFilter;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\InputFilter\ContentNegotiationInputFilter;

class ContentNegotiationInputFilterTest extends TestCase
{
    /**
     * @dataProvider dataProviderIsValidTrue
     */
    public function testIsValidTrue($data)
    {
        $i = new ContentNegotiationInputFilter;
        $i->setData($data);
        $this->assertTrue($i->isValid());
    }

    public function dataProviderIsValidTrue()
    {
        return array(
            array(
                array(
                    'Zend\View\Model\ViewModel' => array('text/html', 'application/xhtml+xml')
                )
            )
        );
    }

    /**
     * @dataProvider dataProviderIsValidFalse
     */
    public function testIsValidFalse($data, $messages)
    {
        $i = new ContentNegotiationInputFilter;
        $i->setData($data);
        $this->assertFalse($i->isValid());
        $this->assertEquals($messages, $i->getMessages());
    }

    public function dataProviderIsValidFalse()
    {
        return array(
            array(
                array(
                    'Zend\View\Model\ViewMode' => array('text/html', 'application/xhtml+xml')
                ),
                array('Zend\View\Model\ViewMode' => array('invalidClassName' => 'Class name is invalid'))
            ),
            array(
                array(
                    'Zend\View\Model\ViewModel' => 'foo'
                ),
                array('Zend\View\Model\ViewModel' => array('invalidMediaTypes' => 'Values for the media-types must be provided as an indexed array'))
            ),
            array(
                array(
                    'Zend\View\Model\ViewModel' => array('texthtml', 'application/xhtml+xml')
                ),
                array('Zend\View\Model\ViewModel' => array('invalidMediaTypes' => 'Invalid media type provided'))
            ),
        );
    }
}
 