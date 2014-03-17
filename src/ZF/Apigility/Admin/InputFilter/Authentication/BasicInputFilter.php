<?php

namespace ZF\Apigility\Admin\InputFilter\Authentication;

use Zend\InputFilter\InputFilter;

class BasicInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name' => 'accept_schemes',
        ));
        $this->add(array(
            'name' => 'realm',
        ));
        $this->add(array(
            'name' => 'htpasswd',
        ));
    }
}
