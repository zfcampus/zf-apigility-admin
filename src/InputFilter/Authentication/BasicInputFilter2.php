<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\Authentication;

class BasicInputFilter2 extends BaseInputFilter
{
    public function init()
    {
        parent::init();

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
