<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter;

use Zend\InputFilter\InputFilter;
use Zend\Stdlib\ArrayUtils;
use Zend\Validator\Callback as CallbackValidator;

class DbAdapterInputFilter extends InputFilter
{
    public function init()
    {
        $this->add(array(
            'name' => 'adapter_name',
            'required' => true,
            'allow_empty' => false,
            'error_message' => 'Please provide a unique, non-empty name for your database connection',
        ));
        $this->add(array(
            'name' => 'database',
            'required' => true,
            'allow_empty' => false,
            'error_message' => 'Please provide the database name; for SQLite, this will be a filesystem path',
        ));
        $this->add(array(
            'name' => 'driver',
            'error_message' => 'Please provide a Database Adapter driver name available to Zend Framework',
        ));
        $this->add(array(
            'name' => 'dsn',
            'required' => false,
            'allow_empty' => true,
        ));
        $this->add(array(
            'name' => 'username',
            'required' => false,
            'allow_empty' => true,
        ));
        $this->add(array(
            'name' => 'password',
            'required' => false,
            'allow_empty' => true,
        ));
        $this->add(array(
            'name' => 'hostname',
            'required' => false,
            'allow_empty' => true,
        ));
        $this->add(array(
            'name' => 'port',
            'required' => false,
            'allow_empty' => true,
            'validators' => array(
                array('name' => 'Digits')
            ),
            'error_message' => 'Please provide a valid port for accessing the database; must be an integer',
        ));
        $this->add(array(
            'name' => 'charset',
            'required' => false,
            'allow_empty' => true,
        ));
        $this->add(array(
            'name' => 'driver_options',
            'required' => false,
            'allow_empty' => true,
            'validators' => array(
                new CallbackValidator(function ($value) {
                    return ArrayUtils::isHashTable($value);
                }),
            ),
            'error_message' => 'Driver options must be provided as a set of key/value pairs',
        ));
    }
}
