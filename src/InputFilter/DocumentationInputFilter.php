<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter;

use Zend\InputFilter\InputFilter;

class DocumentationInputFilter extends InputFilter
{
    protected $messages = array();

    /**
     * Is the data set valid?
     *
     * @return bool
     */
    public function isValid()
    {
        static $validHttpMethods = array('GET', 'POST', 'PUT', 'PATCH', 'DELETE');

        $this->messages = array();
        $isValid = true;

        if (!is_array($this->data)) {
            $this->messages['general']['invalidData'] = 'Documentation payload must be an array';
            return false;
        }

        foreach ($this->data as $key => $data) {

            if (in_array($key, $validHttpMethods)) {

                // valid HTTP method?
                if (isset($this->data['collection']) || isset($this->data['entity'])) {
                    $this->messages[$key]['invalidKey'] = 'HTTP methods cannot be present when "collection" or "entity" is also present';
                    $isValid = false;
                    continue;
                }

                if ($this->isValidHttpMethodDocumentation($data) === false) {
                    $isValid = false;
                    continue;
                }

            } elseif (in_array($key, array('collection', 'entity'))) {

                // valid collection or entity
                if (!is_array($data)) {
                    $this->messages[$key]['invalidData'] = 'Collections and entities methods must be an array of HTTP methods';
                    $isValid = false;
                    continue;
                }
                foreach ($data as $subKey => $subData) {
                    if (in_array($subKey, $validHttpMethods)) {

                        if ($this->isValidHttpMethodDocumentation($subData) === false) {
                            $isValid = false;
                            continue;
                        }

                    } elseif ($subKey === 'description') {
                        if (!is_string($subData)) {
                            $this->messages[$key]['invalidDescription'] = 'Description must be provided as a string';
                            $isValid = false;
                            continue;
                        }
                    } else {
                        $this->messages[$key]['invalidKey'] = 'Key must be description or an HTTP indexed list';
                        $isValid = false;
                        continue;
                    }
                }

            } elseif ($key === 'description') {

                // documentation checking
                if (!is_string($data)) {
                    $this->messages[$key]['invalidDescription'] = 'Description must be provided as a string';
                    $isValid = false;
                    continue;
                }

            } else {

                // everything else is invalid
                $this->messages[$key]['invalidKey'] = 'An invalid key was encountered in the top position, '
                    . 'must be one of an HTTP method, collection, entity, or description';
                $isValid = false;
                continue;
            }

        }

        return $isValid;
    }

    public function isValidHttpMethodDocumentation($data)
    {
        foreach ($data as $key => $value) {
            if (in_array($key, array('description', 'request', 'response'))) {
                if ($value !== null && !is_string($value)) {
                    $this->messages[$key]['invalidElement'] = 'Documentable elements must be strings';
                    return false;
                }
            } else {
                $this->messages[$key]['invalidElement'] = 'Documentable elements must be any or all of description, request or response';
                return false;
            }
        }
        return true;
    }

    public function getMessages()
    {
        return $this->messages;
    }
}
