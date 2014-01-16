<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use ZF\Configuration\ModuleUtils;
use ZF\Configuration\ResourceFactory as ConfigResourceFactory;
use ZF\Configuration\Exception\InvalidArgumentException as InvalidArgumentConfiguration;

class DocumentationModel
{
    /**
     * @var ConfigResourceFactory
     */
    protected $configFactory;

    protected $moduleUtils;

    public function __construct(ConfigResourceFactory $configFactory, ModuleUtils $moduleUtils)
    {
        $this->configFactory = $configFactory;
        $this->moduleUtils = $moduleUtils;
    }

    public function fetchControllerDocumentation($module, $controller)
    {
        $data = $this->getDocumentationArray($module);
        if (!isset($data[$controller]['documentation'])) {
            return null;
        }
        return $data[$controller]['documentation'];
    }

    public function fetchControllerMethodDocumentation($module, $controller, $method, $type)
    {
        $data = $this->getDocumentationArray($module);
        if (!isset($data[$controller][$controller][$method][$type])) {
            return null;
        }
        return $data[$controller][$controller][$method][$type];
    }

    public function storeControllerDocumentation($module, $controller, $text)
    {
        $data = $this->getDocumentationArray($module);
        if (!isset($data[$controller])) {
            $data[$controller] = array('documentation' => '');
        }
        $data[$controller]['documentation'] = $text;
        $this->storeDocumentationArray($module, $data);
    }

    public function storeControllerMethodDocumentation($module, $controller, $method, $type)
    {

    }

    /**
     * Get the validators of a specific module and controller
     *
     * @param  string $module
     * @param  string $controller
     * @param  string $inputFilterName
     * @return false|array|InputFilterEntity
     */
    public function fetch($module, $controller, $method = null, $section = null)
    {
        return $this->getDocumentationArray($module, $controller, $method, $section);
    }

    public function update($module, $controller, $method, $section, $data)
    {
        return $this->storeDocumentationArray($module, $controller, $method, $section, $data);
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

        if (isset($config['zf-rest'])
            && array_key_exists($controller, $config['zf-rest'])
        ) {
            return true;
        }

        if (isset($config['zf-rpc'])
            && array_key_exists($controller, $config['zf-rpc'])
        ) {
            return true;
        }

        return false;
    }

//    /**
//     * Get input filter of a module and controller
//     *
//     * @param  string $module
//     * @param  string $controller
//     * @param  string $inputFilterName
//     * @return false|InputFilterCollection|InputFilterEntity
//     */
//    protected function getDocumentation($module, $controller, $method = null, $section = null)
//    {
//        $moduleConfigPath = $this->moduleUtils->getModuleConfigPath($module);
//        $docConfigPath = dirname($moduleConfigPath) . '/documentation.config.php';
//
//        $docs = (file_exists($docConfigPath)) ? include $docConfigPath : array();
//        if (!isset($docs[$module])) {
//            return null;
//        }
//
//        if (!isset($docs[$module][$controller])) {
//            return null;
//        }
//
//        if (!isset($docs[$module][$controller][$section])) {
//            return null;
//        }
//
//        return $docs[$module][$controller][$section];
//    }

    protected function getDocumentationArray($module)
    {
        $moduleConfigPath = $this->moduleUtils->getModuleConfigPath($module);
        $docConfigPath = dirname($moduleConfigPath) . '/documentation.config.php';
        $docArray = (file_exists($docConfigPath)) ? include $docConfigPath : array();
        if (!is_array($docArray)) {
            $docArray = array();
        }
        return $docArray;
    }

    protected function storeDocumentationArray($module, array $data)
    {
        $moduleConfigPath = $this->moduleUtils->getModuleConfigPath($module);
        $docConfigPath = dirname($moduleConfigPath) . '/documentation.config.php';
        file_put_contents(
            $docConfigPath,
            '<?php' . PHP_EOL . 'return ' . var_export($data, true) . ';' . PHP_EOL
        );
    }

}
