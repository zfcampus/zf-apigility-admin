<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

class ContentNegotiationEntity
{
    public function __construct($name, $config)
    {
        $this->name = $name;
        $this->config = $config;
    }

    /**
     * Retrieve array serialization of entity
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return array_merge(
            array('content_name' => $this->name),
            array('selectors' => $this->config)
        );
    }

    /**
     * Reset state of entity
     *
     * @param  array $array
     */
    public function exchangeArray(array $array)
    {
        $this->config = array();
        foreach ($array as $key => $value) {
            switch (strtolower($key)) {
                case 'content_name':
                    $this->name = $value;
                    break;
                default:
                    $this->config[$key] = $value;
                    break;
            }
        }
    }
}
