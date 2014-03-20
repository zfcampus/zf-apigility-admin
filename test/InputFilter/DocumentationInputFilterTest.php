<?php

namespace ZFTest\Apigility\Admin\InputFilter;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\InputFilter\Factory;

class DocumentationInputFilterTest extends TestCase
{
    public function getInputFilter()
    {
        $factory = new Factory();
        return $factory->createInputFilter(array(
            'type' => 'ZF\Apigility\Admin\InputFilter\DocumentationInputFilter',
        ));
    }

    public function dataProviderIsValid()
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

    public function dataProviderIsInvalid()
    {
        return array(
            'invalid-top-level-keys' => array(
                array('description' => 'foobar', 'Foobar' => 'baz'),
                array(
                    'Foobar' => array('invalidKey' => 'An invalid key was encountered in the top position, must be one of an HTTP method, collection, entity, or description')
                )
            ),
            'collection-or-entity-with-top-level-http-methods' => array(
                array('description' => 'foobar', 'GET' => array('description' => 'foobar'), 'entity' => array()),
                array(
                    'GET' => array('invalidKey' => 'HTTP methods cannot be present when "collection" or "entity" is also present')
                )
            ),
            'http-method-with-bad-format' => array(
                array('description' => 'foobar', 'GET' => array('description' => 'foobar', 'Foo' => 'bar')),
                array(
                    'Foo' => array('invalidElement' => 'Documentable elements must be any or all of description, request or response')
                )
            ),
            'http-method-not-strings' => array(
                array('description' => 'foobar', 'GET' => array('description' => 'foobar', 'request' => 500)),
                array(
                    'request' => array('invalidElement' => 'Documentable elements must be strings')
                )
            ),
            'http-method-not-strings-in-entity' => array(
                array('description' => 'foobar', 'entity' => array('GET' => array('description' => 'foobar', 'response' => 500))),
                array(
                    'response' => array('invalidElement' => 'Documentable elements must be strings')
                )
            ),
            'description-is-not-a-string' => array(
                array('description' => 5),
                array(
                    'description' => array('invalidDescription' => 'Description must be provided as a string')
                )
            ),
            'description-is-not-a-string-in-entity-or-collection' => array(
                array('collection' => array('description' => 5)),
                array(
                    'collection' => array('invalidDescription' => 'Description must be provided as a string')
                )
            ),
            'collection-or-entity-not-an-array' => array(
                array('collection' => 5),
                array(
                    'collection' => array('invalidData' => 'Collections and entities methods must be an array of HTTP methods')
                )
            ),
            'collection-or-entity-using-wrong-key' => array(
                array('collection' => array('Foo' => 'bar')),
                array(
                    'collection' => array('invalidKey' => 'Key must be description or an HTTP indexed list')
                )
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
        $this->assertTrue($filter->isValid());
    }

    /**
     * @dataProvider dataProviderIsInvalid
     */
    public function testIsInvalid($data, $messages)
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        $this->assertFalse($filter->isValid());
        $this->assertEquals($messages, $filter->getMessages());
    }
}
