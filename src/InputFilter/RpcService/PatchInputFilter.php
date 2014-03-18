<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\RpcService;

class PatchInputFilter extends PostInputFilter
{
    public function __construct()
    {
        // service_name
        parent::__construct();

        // module & controller_service_name
        $this->add(array(
            'name' => 'module',
        ));
        $this->add(array(
            'name' => 'controller_service_name',
        ));

        $this->add(array(
            'name' => 'controller_class',
        ));

        // route
        $this->add(array(
            'name' => 'route_name',
        ));
        $this->add(array(
            'name' => 'route_match',
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

        // things that should be ignored / not sent by client
        $this->add(array(
            'name' => 'documentation',
        ));
        $this->add(array(
            'name' => 'input_filter',
        ));
        $this->add(array(
            'name' => '_links',
        ));
        $this->add(array(
            'name' => '_self',
        ));
    }
}
