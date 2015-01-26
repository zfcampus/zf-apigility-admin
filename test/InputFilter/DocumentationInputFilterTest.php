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
            'full-rpc' => array(
                array(
                    'description' => 'Foobar',
                    'GET' => array(
                        'description' => 'another one',
                        'request' => 'request doc',
                        'response' => 'response doc',
                    ),
                    'POST' => array(
                        'description' => 'another one',
                        'request' => 'request doc',
                        'response' => 'response doc',
                    ),
                    'PUT' => array(
                        'description' => 'another one',
                        'request' => 'request doc',
                        'response' => 'response doc',
                    ),
                    'PATCH' => array(
                        'description' => 'another one',
                        'request' => 'request doc',
                        'response' => 'response doc',
                    ),
                    'DELETE' => array(
                        'description' => 'another one',
                        'request' => 'request doc',
                        'response' => 'response doc',
                    ),
                ),
            ),
            // full REST
            'full-rest' => array(
                array(
                    'description' => 'Foobar',
                    'collection' => array(
                        'GET' => array(
                            'description' => 'another one',
                            'request' => 'request doc',
                            'response' => 'response doc',
                        ),
                        'POST' => array(
                            'description' => 'another one',
                            'request' => 'request doc',
                            'response' => 'response doc',
                        ),
                        'PUT' => array(
                            'description' => 'another one',
                            'request' => 'request doc',
                            'response' => 'response doc',
                        ),
                        'PATCH' => array(
                            'description' => 'another one',
                            'request' => 'request doc',
                            'response' => 'response doc',
                        ),
                        'DELETE' => array(
                            'description' => 'another one',
                            'request' => 'request doc',
                            'response' => 'response doc',
                        ),
                    ),
                    'entity' => array(
                        'GET' => array(
                            'description' => 'another one',
                            'request' => 'request doc',
                            'response' => 'response doc',
                        ),
                        'POST' => array(
                            'description' => 'another one',
                            'request' => 'request doc',
                            'response' => 'response doc',
                        ),
                        'PUT' => array(
                            'description' => 'another one',
                            'request' => 'request doc',
                            'response' => 'response doc',
                        ),
                        'PATCH' => array(
                            'description' => 'another one',
                            'request' => 'request doc',
                            'response' => 'response doc',
                        ),
                        'DELETE' => array(
                            'description' => 'another one',
                            'request' => 'request doc',
                            'response' => 'response doc',
                        ),
                    ),
                ),
            ),
            'empty' => array(
                array(),
            ),
        );
    }

    public function dataProviderIsInvalid()
    {
        return array(
            'invalid-top-level-keys' => array(
                array('description' => 'foobar', 'Foobar' => 'baz'),
                array(
                    'Foobar' => array(
                        'An invalid key was encountered in the top position for "Foobar";'
                        . ' must be one of an HTTP method, collection, entity, or description'
                    ),
                ),
            ),
            'collection-or-entity-with-top-level-http-methods' => array(
                array('description' => 'foobar', 'GET' => array('description' => 'foobar'), 'entity' => array()),
                array(
                    'GET' => array(
                        'HTTP methods cannot be present when "collection" or "entity" is also present;'
                        . ' please verify data for "GET"'
                    ),
                ),
            ),
            'http-method-with-bad-format' => array(
                array('description' => 'foobar', 'GET' => array('description' => 'foobar', 'Foo' => 'bar')),
                array(
                    'Foo' => array(
                        'Documentable elements must be any or all of description, request or response;'
                        . ' please verify "Foo"'
                    ),
                ),
            ),
            'http-method-not-strings' => array(
                array('description' => 'foobar', 'GET' => array('description' => 'foobar', 'request' => 500)),
                array(
                    'request' => array('Documentable elements must be strings; please verify "request"'),
                ),
            ),
            'http-method-not-strings-in-entity' => array(
                array(
                    'description' => 'foobar',
                    'entity' => array(
                        'GET' => array('description' => 'foobar', 'response' => 500)
                    )
                ),
                array(
                    'response' => array('Documentable elements must be strings; please verify "response"'),
                ),
            ),
            'description-is-not-a-string' => array(
                array('description' => 5),
                array(
                    'description' => array(
                        'Description must be provided as a string; please verify description for "description"'
                    ),
                ),
            ),
            'description-is-not-a-string-in-entity-or-collection' => array(
                array('collection' => array('description' => 5)),
                array(
                    'collection' => array(
                        'Description must be provided as a string; please verify description for "description"'
                    ),
                ),
            ),
            'collection-or-entity-not-an-array' => array(
                array('collection' => 5),
                array(
                    'collection' => array(
                        'Collections and entities methods must be an array of HTTP methods;'
                        . ' received invalid entry for "collection"'
                    ),
                ),
            ),
            'collection-or-entity-using-wrong-key' => array(
                array('collection' => array('Foo' => 'bar')),
                array(
                    'collection' => array(
                        'Key must be description or an HTTP indexed list; please verify documentation for "Foo"'
                    ),
                ),
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
