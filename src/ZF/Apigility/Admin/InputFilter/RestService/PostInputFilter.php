<?php

namespace ZF\Apigility\Admin\InputFilter\RestService;

use Zend\InputFilter\InputFilter;

class PostInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name' => 'resource_name',
            'validators' => array(
                array(
                    'name' => 'ZF\Apigility\Admin\InputFilter\Validator\ServiceNameValidator',
                ),
            ),
        ));
    }
}
