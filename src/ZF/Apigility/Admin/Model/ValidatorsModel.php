<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\ServiceManager\ServiceManager;

class ValidatorsModel
{
    /**
     * @var array
     */
    protected $validators;

    /**
     * @var ServiceManager
     */
    protected $validatorPlugins;

    /**
     * $validatorPlugins should typically be an instance of 
     * Zend\Validator\ValidatorPluginManager.
     * 
     * @param ServiceManager $validatorPlugins 
     */
    public function __construct(ServiceManager $validatorPlugins)
    {
        $this->validatorPlugins = $validatorPlugins;
    }

    /**
     * @return array
     */
    public function fetchAll()
    {
        return $this->getValidators();
    }

    /**
     * Lazy loads validators from attached plugin manager and sorts them by name
     *
     * @return array
     */
    protected function getValidators()
    {
        if (is_array($this->validators)) {
            return $this->validators;
        }

        $this->validators  = [];
        foreach ($this->validatorPlugins->getRegisteredServices() as $key => $services) {
            $this->validators += $services;
        }
        sort($this->validators, SORT_STRING);
        return $this->validators;
    }
}
