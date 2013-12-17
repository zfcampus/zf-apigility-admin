<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\Stdlib\ArrayUtils;
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
     * @param  string $inputFilterName
     * @return false|array|InputFilterEntity
     */
    public function fetch($module, $controller, $inputFilterName = null)
    {
        return $this->getInputFilter($module, $controller, $inputFilterName);
    }

    /**
     * Update a specific controller with a new inputfilter (validator)
     *
     * @param  string $module
     * @param  string $controller
     * @param  array $inputFilterName
     * @return InputFilterEntity
     */
    public function update($module, $controller, $inputFilter)
    {
        return $this->addInputFilter($module, $controller, $inputFilter);
    }

    /**
     * Remove the named input
     *
     * @param  string $module
     * @param  string $controlller
     * @param  string $inputname
     * @return boolean
     */
    public function remove($module, $controller, $inputname)
    {
        return $this->removeinputfilter($module, $controller, $inputname);
    }

    /**
     * Get input filter of a module and controller
     *
     * @param  string $module
     * @param  string $controller
     * @param  string $inputFilterName
     * @return false|array|InputFilterEntity
     */
    protected function getInputFilter($module, $controller, $inputFilterName = null)
    {
        $configModule = $this->configFactory->factory($module);
        $config       = $configModule->fetch(true);

        if (!isset($config['zf-content-validation'][$controller]['input_filter'])) {
            return array();
        }

        $validator = $config['zf-content-validation'][$controller]['input_filter'];
        if (!array_key_exists($validator, $config['input_filters'])) {
            return false;
        }

        if ($inputFilterName && $inputFilterName !== $validator) {
            return false;
        }

        // Retrieving the input filter by name
        if ($inputFilterName && $inputFilterName === $validator) {
            return new InputFilterEntity($config['input_filters'][$inputFilterName]);
        }

        // Retrieving a collection
        return array(new InputFilterEntity($config['input_filters'][$validator]));
    }

    /**
     * Add input filter
     *
     * @param  string $module
     * @param  string $controller
     * @param  array  $inputFilter
     * @param  string $validatorName
     * @return array|boolean
     */
    protected function addInputFilter($module, $controller, $inputFilter, $validatorName = null)
    {
        if (!$this->controllerExists($module, $controller)) {
            return false;
        }

        $configModule = $this->configFactory->factory($module);
        $config       = $configModule->fetch(true);

        if (!isset($config['zf-content-validation'])) {
            $config['zf-content-validation'] = array();
        }

        if (!isset($config['zf-content-validation'][$controller])) {
            $config['zf-content-validation'][$controller] = [
                'input_filter' => empty($validatorName) ? $this->generateValidatorName($controller) : $validatorName,
            ];
        }

        $validator = $config['zf-content-validation'][$controller]['input_filter'];

        if (!isset($config['input_filters'])) {
            $config['input_filters'] = array();
        }

        if (!isset($config['input_filters'][$validator])) {
            $config['input_filters'][$validator] = array();
        }

        $config['input_filters'][$validator] = ArrayUtils::merge(
            $config['input_filters'][$validator],
            $inputFilter
        );

        $updated = $configModule->patch($config);
        if (!is_array($updated)) {
            return false;
        }

        return new InputFilterEntity($updated['input_filters'][$validator]);
    }

    /**
     * Remove input filter
     *
     * @param  string $module
     * @param  string $controller
     * @param  string $inputFilterName
     * @return boolean
     */
    protected function removeInputFilter($module, $controller, $inputFilterName)
    {
        if (!$this->controllerExists($module, $controller)) {
            return false;
        }

        $configModule = $this->configFactory->factory($module);
        $config       = $configModule->fetch(true);
        $validator    = $config['zf-content-validation'][$controller]['input_filter'];

        if (!isset($config['input_filters'][$validator])) {
            return false;
        }

        if ($inputFilterName && $inputFilterName !== $validator) {
            return false;
        }

        unset($config['input_filters'][$validator]);
        unset($config['zf-content-validation'][$controller]['input_filter']);

        if (empty($config['input_filters'])) {
            unset($config['input_filters']);
        }

        if (empty($config['zf-content-validation'][$controller])) {
            unset($config['zf-content-validation'][$controller]);
        }

        if (empty($config['zf-content-validation'])) {
            unset($config['zf-content-validation']);
        }

        return ($configModule->patch($config) != false);
    }

    /**
     * Generates the validator name based on controller name
     *
     * @param string $controller
     * @return string
     */
    protected function generateValidatorName($controller)
    {
        if (strtolower(substr($controller, -11)) === '\controller' ) {
            return substr($controller, 0, strlen($controller) - 11) . '\Validator';
        }
        return $controller . '\Validator';
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

        $config = $configModule->fetch(true);
        if ((!isset($config['zf-rest']) && !isset($config['zf-rpc'])) 
            || (!array_key_exists($controller, $config['zf-rest']) 
            && !array_key_exists($controller, $config['zf-rpc']))
        ) {
            return false;
        }

        return true;
    }
}
