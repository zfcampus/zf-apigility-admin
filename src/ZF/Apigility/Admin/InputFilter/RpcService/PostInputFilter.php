<?php

namespace ZF\Apigility\Admin\InputFilter\RpcService;

use Zend\InputFilter\InputFilter;

class PostInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name' => 'service_name',
            'validators' => array(
                array(
                    'name' => 'ZF\Apigility\Admin\InputFilter\Validator\ServiceNameValidator',
                ),
            ),
        ));
    }
}
