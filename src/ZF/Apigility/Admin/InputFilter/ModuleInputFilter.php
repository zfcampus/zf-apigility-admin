<?php

namespace ZF\Apigility\Admin\InputFilter;

use Zend\InputFilter\InputFilter;

class ModuleInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name' => 'name',
            'validators' => array(
                array(
                    'name' => 'ZF\Apigility\Admin\InputFilter\Validator\ModuleNameValidator',
                ),
            ),
        ));
    }
}
