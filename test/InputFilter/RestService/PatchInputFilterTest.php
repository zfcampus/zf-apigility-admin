<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\InputFilter\RestService;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\InputFilter\Factory;

class PatchInputFilterTest extends TestCase
{
    public function getInputFilter()
    {
        $factory = new Factory();
        return $factory->createInputFilter(array(
            'type' => 'ZF\Apigility\Admin\InputFilter\RestService\PatchInputFilter',
        ));
    }

    public function dataProviderIsValidTrue()
    {
        return array(
            'all-inputs-present' => array(array(
                'accept_whitelist' => array (
                    0 => 'application/vnd.foo_bar.v1+json',
                    1 => 'application/hal+json',
                    2 => 'application/json',
                ),
                'collection_class' => 'Zend\Paginator\Paginator',
                'collection_http_methods' => array (
                    0 => 'GET',
                    1 => 'POST',
                ),
                'collection_name' => 'foo_bar',
                'collection_query_whitelist' => array (
                ),
                'content_type_whitelist' => array (
                    0 => 'application/vnd.foo_bar.v1+json',
                    1 => 'application/json',
                ),
                'entity_class' => 'StdClass',
                'entity_http_methods' => array (
                    0 => 'GET',
                    1 => 'PATCH',
                    2 => 'PUT',
                    3 => 'DELETE',
                ),
                'entity_identifier_name' => 'id',
                'hydrator_name' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
                'page_size' => 25,
                'page_size_param' => null,
                'resource_class' => 'Foo_Bar\\V1\\Rest\\Baz_Bat\\Baz_BatResource',
                'route_identifier_name' => 'foo_bar_id',
                'route_match' => '/foo_bar[/:foo_bar_id]',
                'selector' => 'HalJson',
                'service_name' => 'Baz_Bat',
            )),
            'page_size-negative' => array(array(
                'accept_whitelist' => array (
                    0 => 'application/vnd.foo_bar.v1+json',
                    1 => 'application/hal+json',
                    2 => 'application/json',
                ),
                'collection_class' => 'Zend\Paginator\Paginator',
                'collection_http_methods' => array (
                    0 => 'GET',
                    1 => 'POST',
                ),
                'collection_name' => 'foo_bar',
                'collection_query_whitelist' => array (
                ),
                'content_type_whitelist' => array (
                    0 => 'application/vnd.foo_bar.v1+json',
                    1 => 'application/json',
                ),
                'entity_class' => 'StdClass',
                'entity_http_methods' => array (
                    0 => 'GET',
                    1 => 'PATCH',
                    2 => 'PUT',
                    3 => 'DELETE',
                ),
                'entity_identifier_name' => 'id',
                'hydrator_name' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
                'page_size' => -1,
                'page_size_param' => null,
                'resource_class' => 'Foo_Bar\\V1\\Rest\\Baz_Bat\\Baz_BatResource',
                'route_identifier_name' => 'foo_bar_id',
                'route_match' => '/foo_bar[/:foo_bar_id]',
                'selector' => 'HalJson',
                'service_name' => 'Baz_Bat',
            )),
        );
    }

    public function dataProviderIsValidFalse()
    {
        return array(
            'missing-service-name' => array(array(
                'accept_whitelist' => array (
                    0 => 'application/vnd.foo_bar.v1+json',
                    1 => 'application/hal+json',
                    2 => 'application/json',
                ),
                'collection_class' => null,
                'collection_http_methods' => array (
                    0 => 'GET',
                    1 => 'POST',
                ),
                'collection_query_whitelist' => array (
                ),
                'content_type_whitelist' => array (
                    0 => 'application/vnd.foo_bar.v1+json',
                    1 => 'application/json',
                ),
                'entity_class' => null,
                'entity_http_methods' => array (
                    0 => 'GET',
                    1 => 'PATCH',
                    2 => 'PUT',
                    3 => 'DELETE',
                ),
                'hydrator_name' => null,
                'page_size' => null,
                'page_size_param' => null,
                'resource_class' => null,
                'route_match' => null,
                'selector' => null,
            ), array(
                'service_name',
            )),
            'empty-inputs' => array(array(
                'accept_whitelist' => array (
                    0 => 'application/vnd.foo_bar.v1+json',
                    1 => 'application/hal+json',
                    2 => 'application/json',
                ),
                'collection_class' => null,
                'collection_http_methods' => array (
                    0 => 'GET',
                    1 => 'POST',
                ),
                'collection_name' => null,
                'collection_query_whitelist' => array (
                ),
                'content_type_whitelist' => array (
                    0 => 'application/vnd.foo_bar.v1+json',
                    1 => 'application/json',
                ),
                'entity_class' => null,
                'entity_http_methods' => array (
                    0 => 'GET',
                    1 => 'PATCH',
                    2 => 'PUT',
                    3 => 'DELETE',
                ),
                'entity_identifier_name' => null,
                'hydrator_name' => null,
                'page_size' => null,
                'page_size_param' => null,
                'resource_class' => null,
                'route_identifier_name' => null,
                'route_match' => null,
                'selector' => null,
                'service_name' => 'Foo_Bar',
            ), array(
                'collection_class',
                'collection_name',
                'entity_class',
                'entity_identifier_name',
                'page_size',
                // 'resource_class', // Resource class is allowed to be empty
                'route_identifier_name',
                'route_match',
            )),
        );
    }

    /**
     * @dataProvider dataProviderIsValidTrue
     */
    public function testIsValidTrue($data)
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        $this->assertTrue($filter->isValid(), var_export($filter->getMessages(), 1));
    }

    /**
     * @dataProvider dataProviderIsValidFalse
     */
    public function testIsValidFalse($data, $expectedInvalidKeys)
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        $this->assertFalse($filter->isValid());

        $messages = $filter->getMessages();
        $testKeys = array_keys($messages);
        sort($expectedInvalidKeys);
        sort($testKeys);
        $this->assertEquals($expectedInvalidKeys, $testKeys);
    }
}
