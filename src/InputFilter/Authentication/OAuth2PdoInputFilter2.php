<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\Authentication;

class OAuth2PdoInputFilter2 extends BaseInputFilter
{
    public function init()
    {
        parent::init();

        $this->add(array(
            'name' => 'oauth2_type',
            'filters' => array(
                array('name' => 'StringToLower'),
            ),
            'validators' => array(
                array(
                    'name' => 'Callback',
                    'options' => array('callback' => function ($value) {
                        return ($value === 'pdo');
                    }),
                ),
            ),
            'error_message' => 'Please provide a valid DSN type adapter (pdo, mongo)',
        ));
        $this->add(array(
            'name' => 'oauth2_dsn',
            'error_message' => 'Please provide a valid DSN for OAuth2 PDO adapter',
        ));
        $this->add(array(
            'name' => 'oauth2_username',
            'error_message' => 'Please provide a username for OAuth2 PDO database',
            'required' => false
        ));
        $this->add(array(
            'name' => 'oauth2_password',
            'error_message' => 'Please provide a password DSN for OAuth2 PDO database',
            'required' => false
        ));
        $this->add(array(
            'name' => 'oauth2_route',
            'validators' => array(
                array(
                    'name' => 'Uri',
                    'options' => array(
                        'allowRelative' => true
                    ),
                ),
            ),
            'error_message' => 'Please provide a valid URL route for OAuth2 PDO adapter'
        ));
        $this->add(array(
            'name' => 'oauth2_options',
            'validators' => array(
                array(
                    'name' => 'Callback',
                    'options' => array('callback' => function ($value) {
                        return is_array($value);
                    }),
                ),
            ),
            'error_message' => 'Please provide a valid options for OAuth2 PDO adapter',
            'required' => false
        ));
    }
}
