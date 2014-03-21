<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter;

use Zend\InputFilter\InputFilter;

class ContentNegotiationInputFilter extends InputFilter
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

        foreach ($this->data as $className => $mediaTypes) {
            if (! class_exists($className)) {
                $this->messages[$className][] = 'Class name (' . $className . ') does not exist';
                $isValid = false;
                continue;
            }

            $interfaces = class_implements($className);
            if (false === $interfaces || ! in_array('Zend\View\Model\ModelInterface', $interfaces)) {
                $this->messages[$className][] = 'Class name (' . $className . ') is invalid; must be a valid Zend\View\Model\ModelInterface class';
                $isValid = false;
                continue;
            }

            if (!is_array($mediaTypes)) {
                $this->messages[$className][] = 'Values for the media-types must be provided as an indexed array';
                $isValid = false;
                continue;
            }

            foreach ($mediaTypes as $mediaType) {
                if (strpos($mediaType, '/') === false) {
                    $this->messages[$className][] = 'Invalid media type (' . $mediaType . ') provided';
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
