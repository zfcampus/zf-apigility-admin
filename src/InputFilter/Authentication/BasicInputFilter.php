<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\Authentication;

use Zend\InputFilter\InputFilter;

class BasicInputFilter extends InputFilter
{
    public function init()
    {
        $this->add(array(
            'name' => 'accept_schemes',
            'validators' => array(
                array(
                    'name' => 'Callback',
                    'options' => array('callback' => function ($value) {
                        if (!is_array($value)) {
                            return false;
                        }
                        $allowed = array('digest', 'basic');
                        foreach ($value as $v) {
                            if (!in_array($v, $allowed)) {
                                return false;
                            }
                        }
                        return true;
                    }),
                ),
            ),
            'error_message' => 'Accept Schemes must be an array containing one or more'
                . ' of the values "basic" or "digest"',
        ));
        $this->add(array(
            'name' => 'realm',
            'error_message' => 'Please provide a realm for HTTP basic authentication',
        ));
        $this->add(array(
            'name' => 'htpasswd',
            'validators' => array(
                array(
                    'name' => 'Callback',
                    'options' => array('callback' => function ($value) {
                        return file_exists($value);
                    }),
                ),
            ),
            'error_message' => 'Path provided for htpasswd file must exist',
        ));
    }
}
