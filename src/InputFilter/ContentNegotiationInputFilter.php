<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter;

use Zend\InputFilter\InputFilterInterface;

class ContentNegotiationInputFilter implements InputFilterInterface
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
        foreach ($this->data as $className => $mediaTypes) {
            if (!class_exists($className, true)) {
                $this->messages[$className]['invalidClassName'] = 'Class name is invalid';
                $isValid = false;
            }

            if (!is_array($mediaTypes)) {
                $this->messages[$className]['invalidMediaTypes'] = 'Values for the media-types must be provided as an indexed array';
                $isValid = false;
                continue;
            }

            foreach ($mediaTypes as $mediaType) {
                if (strpos($mediaType, '/') === false) {
                    $this->messages[$className]['invalidMediaTypes'] = 'Invalid media type provided';
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
