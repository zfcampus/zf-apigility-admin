<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter;

use Zend\InputFilter\InputFilterInterface;

class AuthorizationInputFilter implements InputFilterInterface
{

    protected $data;
    protected $messages = array();

    public function setData($data)
    {
        $this->data = $data;
    }

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
                $this->messages[$className]['invalidClassName'] = 'Class service name is invalid, must be serviceName::method';
                $isValid = false;
            }

            if (!is_array($httpMethods)) {
                $this->messages[$className]['invalidHttpMethod'] = 'Values for each controller must be an http method keyd array of true/false values';
                $isValid = false;
                continue;
            }

            foreach ($httpMethods as $httpMethod => $isRequred) {
                if (!in_array($httpMethod, array('GET', 'POST', 'PUT', 'PATCH', 'DELETE'))) {
                    $this->messages[$className]['invalidHttpMethod'] = 'Invalid header (' . $httpMethod . ') provided.';
                    $isValid = false;
                }
            }
        }

        return $isValid;
    }

    public function getRawValues()
    {
        return $this->data;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    /**#@+
     * Unnecessary methods required by interface for the purposes of this input filter
     * @return void
     */
    public function count()
    {
    }

    public function add($input, $name = null)
    {
    }

    public function get($name)
    {
    }

    public function has($name)
    {
    }

    public function remove($name)
    {
    }

    public function setValidationGroup($name)
    {
    }

    public function getInvalidInput()
    {
    }

    public function getValidInput()
    {
    }

    public function getValue($name)
    {
    }

    public function getValues()
    {
    }

    public function getRawValue($name)
    {
    }
    /**#@-*/
}
