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
            'error_message' => 'Please provide a valid DSN (value will vary based on whether you are selecting Mongo or PDO for the DSN type)',
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
