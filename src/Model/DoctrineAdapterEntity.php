<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\Stdlib\ArraySerializableInterface;

class DoctrineAdapterEntity implements ArraySerializableInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $config;

    /**
     * Constructor
     *
     * @param $name
     * @param $config
     */
    public function __construct($name, $config)
    {
        $this->name = $name;
        $this->config = $config;
    }

    /**
     * Exchange internal values from provided array
     *
     * @param  array $array
     * @return void
     */
    public function exchangeArray(array $array)
    {
        $this->config = array();
        foreach ($array as $key => $value) {
            switch (strtolower($key)) {
                case 'adapter_name':
                    $this->name = $value;
                    break;
                default:
                    $this->config[$key] = $value;
                    break;
            }
        }
    }

    /**
     * Return an array representation of the object
     *
     * @return array
     */
    public function getArrayCopy()
    {
        $baseKey = (isset($this->config['driverClass']))
            ? 'doctrine.entitymanager.'
            : 'doctrine.documentmanager.';

        return array_merge(array(
            'adapter_name' => $baseKey . $this->name,
        ), $this->config);
    }
}
