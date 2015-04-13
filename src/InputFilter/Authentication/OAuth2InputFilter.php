<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\Authentication;

use Zend\InputFilter\InputFilter;

/**
 * @todo DSN validation
 */
class OAuth2InputFilter extends InputFilter
{
    public function init()
    {
        $this->add(array(
            'name' => 'dsn',
            'continue_if_empty' => true,
            'validators' => array(
                array(
                    'name' => 'Callback',
                    'options' => array('callback' => function ($value, $context) {
                        if (! isset($context['dsn_type']) || empty($context['dsn_type'])) {
                            // PDO is default DSN type; mark as invalid if none provided
                            return false;
                        }

                        if ($context['dsn_type'] === 'Mongo') {
                            /**
                             * @todo Mongo DSN validation should move out of model to here
                             */
                            return true;
                        }

                        if (! is_string($value)) {
                            return false;
                        }

                        if (empty($value)) {
                            return false;
                        }

                        /**
                         * @todo PDO DSN validation should move out of model to here
                         */
                        return true;
                    }),
                ),
            ),
            'error_message' => 'Please provide a valid DSN (value will vary based on'
                . ' whether you are selecting Mongo or PDO for the DSN type)',
        ));
        $this->add(array(
            'name' => 'database',
            'continue_if_empty' => true,
            'validators' => array(
                array(
                    'name' => 'Callback',
                    'options' => array('callback' => function ($value, $context) {
                        if (! isset($context['dsn_type']) || $context['dsn_type'] !== 'Mongo') {
                            // Database is only relevant to Mongo
                            return true;
                        }

                        if (! is_string($value)) {
                            return false;
                        }

                        if (empty($value)) {
                            return false;
                        }

                        return true;
                    }),
                ),
            ),
            'error_message' => 'Please provide a valid Mongo database',
        ));
        $this->add(array(
            'name' => 'dsn_type',
            'validators' => array(
                array(
                    'name' => 'InArray',
                    'options' => array('haystack' => array(
                        'PDO',
                        'Mongo',
                    )),
                ),
            ),
            'error_message' => 'Indicate whether you are using Mongo or PDO',
        ));
        $this->add(array(
            'name' => 'username',
            'required' => false,
        ));
        $this->add(array(
            'name' => 'password',
            'required' => false,
        ));
        $this->add(array(
            'name' => 'route_match',
            'error_message' => 'Please provide a valid URI path for where OAuth2 will respond',
        ));
    }
}
