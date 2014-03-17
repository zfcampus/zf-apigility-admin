<?php

namespace ZF\Apigility\Admin\InputFilter\RestService;

class PatchInputFilter extends PostInputFilter
{
    public function __construct()
    {
        parent::__construct();
        $this->add(array(
            'name' => 'accept_whitelist',
        ));
        $this->add(array(
            'name' => 'content_type_whitelist',
        ));
        $this->add(array(
            'name' => 'selector',
        ));
        $this->add(array(
            'name' => 'route_match',
        ));
        $this->add(array(
            'name' => 'page_size',
            'validators' => array(
                array('name' => 'Zend\Validator\Digits')
            )
        ));
        $this->add(array(
            'name' => 'collection_http_methods',
        ));
        $this->add(array(
            'name' => 'entity_http_methods',
        ));
        $this->add(array(
            'name' => 'route_identifier_name',
        ));
        $this->add(array(
            'name' => 'entity_identifier_name',
        ));
        $this->add(array(
            'name' => 'hydrator_name',
        ));
        $this->add(array(
            'name' => 'collection_name',
        ));
        $this->add(array(
            'name' => 'page_size_param',
        ));
        $this->add(array(
            'name' => 'collection_query_whitelist',
        ));
        $this->add(array(
            'name' => 'entity_class',
        ));
        $this->add(array(
            'name' => 'collection_class',
        ));

    }
}
