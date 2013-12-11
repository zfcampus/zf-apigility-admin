<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use ZF\Configuration\ResourceFactory as ConfigResourceFactory;
use ZF\Configuration\Exception\InvalidArgumentException as InvalidArgumentConfiguration;

class InputFilterModel
{
    /**
     * @var ConfigResourceFactory
     */
    protected $configFactory;

    /**
     * $validatorPlugins should typically be an instance of 
     * Zend\Validator\ValidatorPluginManager.
     * 
     * @param ServiceManager $validatorPlugins 
     */
    public function __construct(ConfigResourceFactory $configFactory)
    {
        $this->configFactory = $configFactory;
    }

    /**
     * Get the validators of a specific module and controller
     *
     * @param  string $module
     * @param  string $controller
     * @param  string $inputname
     * @return array
     */
    public function fetch($module, $controller, $inputname = null)
    {
        return $this->getInputFilter($module, $controller, $inputname);
    }

    /**
     * Get input filter of a module and controller
     *
     * @param  string $module
     * @param  string $controller
     * @param  string $inputname
     * @return array|boolean
     */
    protected function getInputFilter($module, $controller, $inputname = null)
    {
        $configModule = $this->configFactory->factory($module);
        $config       = $configModule->fetch();
        if (! array_key_exists($controller, $config['zf-content-validation'])) {
            return false;
        }

        $validator = $config['zf-content-validation']['input_filter'];
        if ($inputname && !array_key_exists($inputname, $config['input_filters'][$validator])) {
            return false;
        }

        if ($inputname) {
            return $config['input_filters'][$validator][$inputname];
        } else {
            return $config['input_filters'][$validator];
        }
    }

    protected function addInputfilter($module, $service, $inputfilter)
    {
    }

    protected function removeInputfilter($module, $service, $inputfilter)
    {
    }

    /**
     * Check if the module exists
     *
     * @param  string $module
     * @return boolean
     */
    public function moduleExists($module)
    {
        try {
            $configModule = $this->configFactory->factory($module);
        } catch (InvalidArgumentConfiguration $e) {
            return false;
        }
        return true;
    }

    /**
     * Check if a module and controller exists
     *
     * @param  string $module
     * @param  string $controller
     * @return boolean
     */
    public function controllerExists($module, $controller)
    {
        try {
            $configModule = $this->configFactory->factory($module);
        } catch (InvalidArgumentConfiguration $e) {
            return false;
        }
        if (!array_key_exists($controller, $configModule['zf-rest']) &&
            !array_key_exists($controller, $configModule['zf-rpc'])) {
            return false;
        }
        return true;
    }
}
