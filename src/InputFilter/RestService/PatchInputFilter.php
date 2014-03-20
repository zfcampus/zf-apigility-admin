<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\RestService;

class PatchInputFilter extends PostInputFilter
{
    protected $isUpdate = true;

    public function init()
    {
        parent::init();

        // classes
        $this->add(array(
            'name' => 'resource_class',
            'required' => true,
            'allow_empty' => false,
        ));
        $this->add(array(
            'name' => 'collection_class',
            'required' => true,
            'allow_empty' => false,
        ));
        $this->add(array(
            'name' => 'entity_class',
            'required' => true,
            'allow_empty' => false,
        ));

        $this->add(array(
            'name' => 'route_match',
            'required' => true,
            'allow_empty' => false,
        ));

        $this->add(array(
            'name' => 'accept_whitelist',
            'required' => true,
            'allow_empty' => true,
            'continue_if_empty' => true,
        ));
        $this->add(array(
            'name' => 'content_type_whitelist',
            'required' => true,
            'allow_empty' => true,
            'continue_if_empty' => true,
        ));
        $this->add(array(
            'name' => 'selector',
            'required' => false,
            'allow_empty' => true,
        ));

        $this->add(array(
            'name' => 'page_size',
            'required' => false,
            'allow_empty' => true,
            'continue_if_empty' => true,
            'validators' => array(
                array('name' => 'Zend\Validator\Digits')
            )
        ));
        $this->add(array(
            'name' => 'collection_http_methods',
            'required' => true,
            'allow_empty' => true,
            'continue_if_empty' => true,
            'validators' => array(
                array('name' => 'ZF\Apigility\Admin\InputFilter\Validator\HttpMethodArrayValidator')
            )
        ));
        $this->add(array(
            'name' => 'entity_http_methods',
            'required' => true,
            'allow_empty' => true,
            'continue_if_empty' => true,
            'validators' => array(
                array('name' => 'ZF\Apigility\Admin\InputFilter\Validator\HttpMethodArrayValidator')
            )
        ));
        $this->add(array(
            'name' => 'route_identifier_name',
            'required' => true,
            'allow_empty' => false,
        ));
        $this->add(array(
            'name' => 'entity_identifier_name',
            'required' => true,
            'allow_empty' => false,
        ));
        $this->add(array(
            'name' => 'hydrator_name',
            'required' => true,
            'allow_empty' => true,
            'continue_if_empty' => true,
        ));
        $this->add(array(
            'name' => 'collection_name',
            'required' => true,
            'allow_empty' => false,
        ));
        $this->add(array(
            'name' => 'page_size_param',
            'required' => true,
            'allow_empty' => true,
            'continue_if_empty' => true,
        ));
        $this->add(array(
            'name' => 'collection_query_whitelist',
            'required' => true,
            'allow_empty' => true,
            'continue_if_empty' => true,
        ));
    }
}
