<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter;

use Zend\InputFilter\InputFilter;

class AuthorizationInputFilter extends InputFilter
{
    protected $messages = array();

    /**
     * Is the data set valid?
     *
     * @return bool
     */
    public function isValid()
    {
        $this->messages = array();
        $isValid = true;
        foreach ($this->data as $className => $httpMethods) {
            // validate the structure of the controller service name / method
            if (strpos($className, '::') === false) {
                $this->messages[$className][] = 'Class service name is invalid, must be serviceName::method,'
                    . ' serviceName::__collection__, or serviceName::__entity__';
                $isValid = false;
            }

            if (!is_array($httpMethods)) {
                $this->messages[$className][] = 'Values for each controller must be an http method'
                    . ' keyed array of true/false values';
                $isValid = false;
                continue;
            }

            foreach ($httpMethods as $httpMethod => $isRequired) {
                if (!in_array($httpMethod, array('GET', 'POST', 'PUT', 'PATCH', 'DELETE'))) {
                    $this->messages[$className][] = 'Invalid HTTP method (' . $httpMethod . ') provided.';
                    $isValid = false;
                }
            }
        }

        return $isValid;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
