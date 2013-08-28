<?php

namespace ZF\ApiFirstAdmin\Model;

class CodeConnectedRest
{
    /**
     * @var ConfigResource
     */
    protected $configResource;

    /**
     * @var string
     */
    protected $module;

    /**
     * @var ModuleUtils
     */
    protected $modules;

    /**
     * @param  string $module 
     * @param  ModuleUtils $modules 
     * @param  ConfigResource $config 
     */
    public function __construct($module, ModuleUtils $modules, ConfigResource $config)
    {
        $this->module         = $module;
        $this->modules        = $modules;
        $this->configResource = $config;
    }

    /**
     * @todo Create a RestEndpointMetadata class - it can extend the 
     *       RestCreationEndpoint, but compose the module, controller class name, 
     *       etc. Munge data from all config sources and pass them to an instance
     *       of this class.
     * @param  string $controllerService
     * @return RestEndpointMetadata|false
     */
    public function fetch ($controllerService)
    {
    }

    public function createService(RestCreationEndpoint $details)
    {
        $resourceName      = $details->resourceName;
        $controllerService = sprintf('%s\\Controller\\%s', $this->module, $resourceName);
        $resourceClass     = $this->createResourceClass($resourceName);
        $entityClass       = $this->createEntityClass($resourceName);
        $collectionClass   = $this->createCollectionClass($resourceName);
        $routeName         = $this->createRoute($details->route, $controllerService, $details->identifierName);
        $this->createRestConfig($details, $resourceClass, $routeName);
        $this->createContentNegotiationConfig($details);
        $this->createHalConfig($details, $entityClass, $collectionClass);

        return $this->fetch($controllerService);
    }

    protected function createControllerServiceName($resourceName, $module)
    {
        return sprintf('%s\\Controller\\%s', $module, $resourceName);
    }
}
