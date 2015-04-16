<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\Authentication;

use Zend\InputFilter\InputFilter;

class BaseInputFilter extends InputFilter
{
    public function init()
    {
        $this->add(array(
            'name' => 'name',
            'error_message' => 'Please provide a name for HTTP authentication',
            'filters' => array(
                array('name' => 'StringToLower'),
            )
        ));
        $this->add(array(
            'name' => 'type',
            'error_message' => 'Please provide the HTTP authentication type',
            'filters' => array(
                array('name' => 'StringToLower'),
            ),
            'validators' => array(
                array(
                    'name' => 'Callback',
                    'options' => array('callback' => function ($value) {
                        return in_array($value, array('basic', 'digest', 'oauth2'));
                    }),
                )
            )
        ));
    }
}
