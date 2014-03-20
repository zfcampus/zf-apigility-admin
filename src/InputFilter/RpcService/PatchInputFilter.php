<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\RpcService;

class PatchInputFilter extends PostInputFilter
{
    public function init()
    {
        // service_name
        parent::init();

        // module & controller_service_name
        $this->add(array(
            'name' => 'module',
        ));

        // route
        $this->add(array(
            'name' => 'route_match',
        ));

        $this->add(array(
            'name' => 'accept_whitelist',
            'validators' => array(
                array('name' => 'ZF\Apigility\Admin\InputFilter\Validator\MediaTypeArrayValidator')
            )
        ));
        $this->add(array(
            'name' => 'content_type_whitelist',
            'validators' => array(
                array('name' => 'ZF\Apigility\Admin\InputFilter\Validator\MediaTypeArrayValidator')
            )
        ));
        $this->add(array(
            'name' => 'selector',
        ));
        $this->add(array(
            'name' => 'http_methods',
            'validators' => array(
                array('name' => 'ZF\Apigility\Admin\InputFilter\Validator\HttpMethodArrayValidator')
            )
        ));
    }
}
