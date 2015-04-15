<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\Authentication;

class OAuth2MongoInputFilter2 extends BaseInputFilter
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
                        return ($value === 'mongo');
                    }),
                ),
            ),
            'error_message' => 'Please provide a valid DSN type adapter (pdo, mongo)',
        ));
        $this->add(array(
            'name' => 'oauth2_dsn',
            'error_message' => 'Please provide a valid DSN for OAuth2 database',
            'required' => false
        ));
        $this->add(array(
            'name' => 'oauth2_database',
            'error_message' => 'Please provide a valid database name for OAuth2 Mongo adapter'
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
            'error_message' => 'Please provide a valid URL route for OAuth2 Mongo adapter'
        ));
        $this->add(array(
            'name' => 'oauth2_locator_name',
            'error_message' => 'Please provide a valid locator name for OAuth2 Mongo adapter',
            'required' => false
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
            'error_message' => 'Please provide a valid options for OAuth2 Mongo adapter',
            'required' => false
        ));
    }
}
