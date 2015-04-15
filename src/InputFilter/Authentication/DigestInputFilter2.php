<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\Authentication;

class DigestInputFilter2 extends BaseInputFilter
{
    public function init()
    {
        parent::init();

        $this->add(array(
            'name' => 'realm',
            'error_message' => 'Please provide a realm for HTTP digest authentication',
        ));
        $this->add(array(
            'name' => 'digest_domains',
            'error_message' => 'Please provide a digest domains for HTTP digest authentication',
            'required' => false
        ));
        $this->add(array(
            'name' => 'nonce_timeout',
            'validators' => array(
                array(
                    'name' => 'Callback',
                    'options' => array('callback' => function ($value) {
                        return is_numeric($value);
                    }),
                ),
            ),
            'error_message' => 'Please provide a valid nonce timeout for HTTP digest authentication'
        ));
        $this->add(array(
            'name' => 'htdigest',
            'validators' => array(
                array(
                    'name' => 'Callback',
                    'options' => array('callback' => function ($value) {
                        return file_exists($value);
                    }),
                ),
            ),
            'error_message' => 'Path provided for htdigest file must exist',
        ));
    }
}
