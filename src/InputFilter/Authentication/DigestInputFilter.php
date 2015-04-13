<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\Authentication;

use Zend\InputFilter\InputFilter;

class DigestInputFilter extends InputFilter
{
    public function init()
    {
        $this->add(array(
            'name' => 'accept_schemes',
            'validators' => array(
                array(
                    'name' => 'Callback',
                    'options' => array('callback' => function ($value) {
                        if (! is_array($value)) {
                            return false;
                        }
                        $allowed = array('digest', 'basic');
                        foreach ($value as $v) {
                            if (! in_array($v, $allowed)) {
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
        $this->add(array(
            'name' => 'nonce_timeout',
            'validators' => array(
                array('name' => 'Zend\Validator\Digits')
            ),
            'error_message' => 'Nonce Timeout must be an integer',
        ));
        $this->add(array(
            'name' => 'digest_domains',
            'error_message' => 'Digest Domains must be provided as a string',
        ));
    }
}
