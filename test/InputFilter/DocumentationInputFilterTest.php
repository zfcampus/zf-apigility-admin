<?php

namespace ZFTest\Apigility\Admin\InputFilter;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\InputFilter\DocumentationInputFilter;

class DocumentationInputFilterTest extends TestCase
{
    /**
     * @dataProvider dataProviderIsValidTrue
     */
    public function testIsValidTrue($data)
    {
        $i = new DocumentationInputFilter;
        $i->setData($data);
        $this->assertTrue($i->isValid());
    }

    public function dataProviderIsValidTrue()
    {
        return array(
            // full RPC
            array(
                array(
                    'description' => 'Foobar',
                    'GET' => array(
                        'description' => 'another one',
                        'request' => 'request doc',
                        'response' => 'response doc'
                    ),
                    'POST' => array(
                        'description' => 'another one',
                        'request' => 'request doc',
                        'response' => 'response doc'
                    ),
                    'PUT' => array(
                        'description' => 'another one',
                        'request' => 'request doc',
                        'response' => 'response doc'
                    ),
                    'PATCH' => array(
                        'description' => 'another one',
                        'request' => 'request doc',
                        'response' => 'response doc'
                    ),
                    'DELETE' => array(
                        'description' => 'another one',
                        'request' => 'request doc',
                        'response' => 'response doc'
                    ),
                )
            ),
            // full REST
            array(
                array(
                    'description' => 'Foobar',
                    'collection' => array(
                        'GET' => array(
                            'description' => 'another one',
                            'request' => 'request doc',
                            'response' => 'response doc'
                        ),
                        'POST' => array(
                            'description' => 'another one',
                            'request' => 'request doc',
                            'response' => 'response doc'
                        ),
                        'PUT' => array(
                            'description' => 'another one',
                            'request' => 'request doc',
                            'response' => 'response doc'
                        ),
                        'PATCH' => array(
                            'description' => 'another one',
                            'request' => 'request doc',
                            'response' => 'response doc'
                        ),
                        'DELETE' => array(
                            'description' => 'another one',
                            'request' => 'request doc',
                            'response' => 'response doc'
                        ),
                    ),
                    'entity' => array(
                        'GET' => array(
                            'description' => 'another one',
                            'request' => 'request doc',
                            'response' => 'response doc'
                        ),
                        'POST' => array(
                            'description' => 'another one',
                            'request' => 'request doc',
                            'response' => 'response doc'
                        ),
                        'PUT' => array(
                            'description' => 'another one',
                            'request' => 'request doc',
                            'response' => 'response doc'
                        ),
                        'PATCH' => array(
                            'description' => 'another one',
                            'request' => 'request doc',
                            'response' => 'response doc'
                        ),
                        'DELETE' => array(
                            'description' => 'another one',
                            'request' => 'request doc',
                            'response' => 'response doc'
                        ),
                    )
                )
            ),
            // empty array
            array(
                array()
            )
        );
    }

    /**
     * @dataProvider dataProviderIsValidFalse
     */
    public function testIsValidFalse($data, $messages)
    {
        $i = new DocumentationInputFilter;
        $i->setData($data);
        $this->assertFalse($i->isValid());
        $this->assertEquals($messages, $i->getMessages());
    }

    public function dataProviderIsValidFalse()
    {
        return array(
            // not an array
            array(
                null,
                array(
                    'general' => array('invalidData' => 'Documentation payload must be an array')
                )
            ),
            // bad top keys
            array(
                array('description' => 'foobar', 'Foobar' => 'baz'),
                array(
                    'Foobar' => array('invalidKey' => 'An invalid key was encountered in the top position, must be one of an HTTP method, collection, entity, or description')
                )
            ),
            // collection|entity with http methods in top
            array(
                array('description' => 'foobar', 'GET' => array('description' => 'foobar'), 'entity' => array()),
                array(
                    'GET' => array('invalidKey' => 'HTTP methods cannot be present when "collection" or "entity" is also present')
                )
            ),
            // http method bad format
            array(
                array('description' => 'foobar', 'GET' => array('description' => 'foobar', 'Foo' => 'bar')),
                array(
                    'Foo' => array('invalidElement' => 'Documentable elements must be any or all of description, request or response')
                )
            ),
            // http method not strings
            array(
                array('description' => 'foobar', 'GET' => array('description' => 'foobar', 'request' => 500)),
                array(
                    'request' => array('invalidElement' => 'Documentable elements must be strings')
                )
            ),
            // http method not strings in entity
            array(
                array('description' => 'foobar', 'entity' => array('GET' => array('description' => 'foobar', 'response' => 500))),
                array(
                    'response' => array('invalidElement' => 'Documentable elements must be strings')
                )
            ),
            // description not a string
            array(
                array('description' => 5),
                array(
                    'description' => array('invalidDescription' => 'Description must be provided as a string')
                )
            ),
            // description not a string in entity|collection
            array(
                array('collection' => array('description' => 5)),
                array(
                    'collection' => array('invalidDescription' => 'Description must be provided as a string')
                )
            ),
            // collection | entity must be an array
            array(
                array('collection' => 5),
                array(
                    'collection' => array('invalidData' => 'Collections and entities methods must be an array of HTTP methods')
                )
            ),
            // collection | entity wrong key
            array(
                array('collection' => array('Foo' => 'bar')),
                array(
                    'collection' => array('invalidKey' => 'Key must be description or an HTTP indexed list')
                )
            ),
        );
    }
}
