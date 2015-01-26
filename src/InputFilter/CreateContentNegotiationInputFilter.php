<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter;

class CreateContentNegotiationInputFilter extends ContentNegotiationInputFilter
{
    /**
     * Is the data set valid?
     *
     * @return bool
     */
    public function isValid()
    {
        $this->messages = array();
        $isValid = true;

        if (! array_key_exists('content_name', $this->data)) {
            $this->messages['content_name'][] = 'No content_name was provided; must be present for new negotiators.';
            $isValid = false;
        }

        if (array_key_exists('content_name', $this->data) && ! is_string($this->data['content_name'])) {
            $this->messages['content_name'][] = 'Content name provided is invalid; must be a string';
            $isValid = false;
        }

        if (! $isValid) {
            return false;
        }

        $contentName = $this->data['content_name'];
        unset($this->data['content_name']);

        $isValid = parent::isValid();

        $this->data['content_name'] = $contentName;

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
