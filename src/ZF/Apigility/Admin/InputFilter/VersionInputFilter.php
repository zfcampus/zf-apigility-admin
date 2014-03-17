<?php

namespace ZF\Apigility\Admin\InputFilter;

use Zend\InputFilter\InputFilter;

class VersionInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name' => 'module',
            'validators' => array(
                array('name' => 'ZF\Apigility\Admin\InputFilter\Validator\ModuleNameValidator'),
            ),
        ));
        $this->add(array(
            'name' => 'version',
            'validators' => array(
                array('name' => 'Zend\Validator\Digits')
            )
        ));
    }
}
