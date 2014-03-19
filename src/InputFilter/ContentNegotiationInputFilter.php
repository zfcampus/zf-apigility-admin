<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter;

use Zend\InputFilter\InputFilter;

class ContentNegotiationInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name' => '',
            'validators' => array(
                array(
                    'name' => 'Callback',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\Callback::INVALID_VALUE => 'Must be an array',
                        ),
                        'callback' => function ($value, $context) {
                                return is_array($value);
                            }
                    )
                )
            )
        ));
    }
}
