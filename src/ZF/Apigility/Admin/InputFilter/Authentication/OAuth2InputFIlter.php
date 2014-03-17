<?php

namespace ZF\Apigility\Admin\InputFilter\Authentication;

use Zend\InputFilter\InputFilter;


class OAuth2InputFIlter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name' => 'dsn',
        ));
        $this->add(array(
            'name' => 'username',
        ));
        $this->add(array(
            'name' => 'password',
        ));
        $this->add(array(
            'name' => 'route_match',
        ));
    }
}
