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
    const NULL_DESCRIPTION = null;

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
        // check to ensure target is correct
        if ($httpMethod) {
            if (!in_array($target, [self::TARGET_DESCRIPTION, self::TARGET_REQUEST, self::TARGET_RESPONSE])) {
                throw new \InvalidArgumentException('$target must be one of description, request, or response');
            }
        } elseif ($target !== self::TARGET_DESCRIPTION) {
            throw new \InvalidArgumentException('$target must be description when $httpMethod is null');
        }

        //$data = $this->getDocumentationArray($module);

        $config = $this->getDocumentationConfigResource($module);
        $flatConfigValues = $config->fetch();

        $dottedName = $controllerServiceName . '.';

        // create flat key
        if ($restResourceType) {
            $dottedName .= $restResourceType . '.';
            if ($httpMethod) {
                $dottedName .= $httpMethod . '.';
            }
        }

        $dottedName .= $target;
        if (isset($flatConfigValues[$dottedName])) {
            return $flatConfigValues[$dottedName];
        }
        return null;
    }

    public function storeRestDocumentation($documentation, $module, $controllerServiceName, $restResourceType = null, $httpMethod = null, $target = self::TARGET_DESCRIPTION)
    {
        // check to ensure target is correct
        if ($httpMethod) {
            if (!in_array($target, [self::TARGET_DESCRIPTION, self::TARGET_REQUEST, self::TARGET_RESPONSE])) {
                throw new \InvalidArgumentException('$target must be one of description, request, or response');
            }
        } elseif ($target !== self::TARGET_DESCRIPTION) {
            throw new \InvalidArgumentException('$target must be description when $httpMethod is null');
        }


        $config = $this->getDocumentationConfigResource($module);
        $dottedName = $controllerServiceName . '.';

        // create flat key
        if ($restResourceType) {
            $dottedName .= $restResourceType . '.';
            if ($httpMethod) {
                $dottedName .= $httpMethod . '.';
            }
        }

        $dottedName .= $target;

        $config->patchKey($dottedName, $documentation);
        return $documentation;
    }

    public function fetchRpcDocumentation($module, $controllerServiceName, $httpMethod, $target = self::TARGET_DESCRIPTION)
    {
        // check to ensure target is correct
        if ($httpMethod) {
            if (!in_array($target, [self::TARGET_DESCRIPTION, self::TARGET_REQUEST, self::TARGET_RESPONSE])) {
                throw new \InvalidArgumentException('$target must be one of description, request, or response');
            }
        } elseif ($target !== self::TARGET_DESCRIPTION) {
            throw new \InvalidArgumentException('$target must be description when $httpMethod is null');
        }

        $config = $this->getDocumentationConfigResource($module);
        $flatConfigValues = $config->fetch();

        $dottedName = $controllerServiceName . '.';

        // create flat key
        if ($httpMethod) {
            $dottedName .= $httpMethod . '.';
        }

        $dottedName .= $target;
        if (isset($flatConfigValues[$dottedName])) {
            return $flatConfigValues[$dottedName];
        }
        return null;
    }

    public function storeRpcDocumentation($documentation, $module, $controllerServiceName, $httpMethod, $target = self::TARGET_DESCRIPTION)
    {
        // check to ensure target is correct
        if ($httpMethod) {
            if (!in_array($target, [self::TARGET_DESCRIPTION, self::TARGET_REQUEST, self::TARGET_RESPONSE])) {
                throw new \InvalidArgumentException('$target must be one of description, request, or response');
            }
        } elseif ($target !== self::TARGET_DESCRIPTION) {
            throw new \InvalidArgumentException('$target must be description when $httpMethod is null');
        }

        $config = $this->getDocumentationConfigResource($module);

        $dottedName = $controllerServiceName . '.';

        // create flat key
        if ($httpMethod) {
            $dottedName .= $httpMethod . '.';
        }

        $dottedName .= $target;

        $config->patchKey($dottedName, $documentation);
        return $documentation;
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

    /**
     * @param $module
     * @return \ZF\Configuration\ConfigResource
     */
    protected function getDocumentationConfigResource($module)
    {
        $moduleConfigPath = $this->moduleUtils->getModuleConfigPath($module);
        $docConfigPath = dirname($moduleConfigPath) . '/documentation.config.php';
        $docArray = (file_exists($docConfigPath)) ? include $docConfigPath : array();
        return $this->configFactory->createConfigResource($docArray, $docConfigPath);
    }

}
