<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\RpcService;

use Zend\InputFilter\InputFilter;

class PostInputFilter extends InputFilter
{
    public function init()
    {
        $this->add(array(
            'name' => 'service_name',
            'validators' => array(
                array(
                    'name' => 'ZF\Apigility\Admin\InputFilter\Validator\ServiceNameValidator',
                ),
            ),
            'error_message' => 'Service Name is required, and must be a valid PHP class name',
        ));
        $this->add(array(
            'name' => 'route_match',
            'error_message' => 'Route Match is required, and must be a valid URI path',
        ));
    }
}
