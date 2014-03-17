<?php

namespace ZF\Apigility\Admin\InputFilter\RpcService;

class PatchInputFilter extends PostInputFilter
{
    public function __construct()
    {
        parent::__construct();
        $this->add(array(
            'name' => 'route',
        ));
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
            'name' => 'http_methods',
        ));


    }
}
