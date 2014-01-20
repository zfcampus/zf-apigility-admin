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
    const REST_RESOURCE_ENTITY = 'entity';
    const REST_RESOURCE_COLLECTION = 'collection';
    const TARGET_DESCRIPTION = 'description';
    const TARGET_REQUEST = 'request';
    const TARGET_RESPONSE = 'response';

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

    public function fetchRestDocumentation($module, $controllerServiceName, $restResourceType = null, $httpMethod = null, $target = self::TARGET_DESCRIPTION)
    {
        $data = $this->getDocumentationArray($module);
        if (!isset($data[$controllerServiceName])) {
            return null;
        }
    }

    public function storeRestDocumentation($documentation, $module, $controllerServiceName, $restResourceType = null, $httpMethod = null, $target = self::TARGET_DESCRIPTION)
    {
        $data = $this->getDocumentationArray($module);
    }

    public function fetchRpcDocumentation($module, $controllerServiceName, $httpMethod, $target = self::TARGET_DESCRIPTION)
    {
        $data = $this->getDocumentationArray($module);
    }

    public function storeRpcDocumentation($documentation, $module, $controllerServiceName, $httpMethod, $target = self::TARGET_DESCRIPTION)
    {
        $data = $this->getDocumentationArray($module);
    }

    public function fetchControllerDocumentation($module, $controller)
    {
        $data = $this->getDocumentationArray($module);
        if (!isset($data[$controller]['documentation'])) {
            return null;
        }
        return $data[$controller]['documentation'];
    }

    public function fetchControllerMethodDocumentation($module, $controller, $type, $method)
    {
        $data = $this->getDocumentationArray($module);
        if (!isset($data[$controller][$method][$type])) {
            return null;
        }
        return $data[$controller][$method][$type];
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

    public function storeControllerMethodDocumentation($module, $controller, $method, $section, $text)
    {
        $data = $this->getDocumentationArray($module);
        if (!isset($data[$controller])) {
            $data[$controller] = array();
        }
        if (!isset($data[$controller][$method])) {
            $data[$controller][$method] = [];
        }
        if (!isset($data[$controller][$method][$section])) {
            $data[$controller][$method][$section] = '';
        }
        $data[$controller][$method][$section] = $text;
        $this->storeDocumentationArray($module, $data);
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
