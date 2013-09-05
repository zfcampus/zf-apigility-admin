<?php

namespace ZF\ApiFirstAdmin\Model;

use Zend\Filter\FilterChain;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use ZF\ApiFirstAdmin\Exception;
use ZF\Configuration\ConfigResource;
use ZF\Configuration\ModuleUtils;

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
     * @var string
     */
    protected $modulePath;

    /**
     * @var ModuleUtils
     */
    protected $modules;

    /**
     * @var PhpRenderer
     */
    protected $renderer;

    /**
     * Allowed REST update options that are scalars
     *
     * @var array
     */
    protected $restScalarUpdateOptions = array(
        'pageSize'                 => 'page_size',
        'pageSizeParam'            => 'page_size_param',
    );

    /**
     * Allowed REST update options that are arrays
     *
     * @var array
     */
    protected $restArrayUpdateOptions = array(
        'collectionHttpOptions'    => 'collection_http_options',
        'collectionQueryWhitelist' => 'collection_query_whitelist',
        'resourceHttpOptions'      => 'resource_http_options',
    );

    /**
     * @var FilterChain
     */
    protected $routeNameFilter;

    /**
     * @var string
     */
    protected $sourcePath;

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
        $this->modulePath     = $modules->getModulePath($module);
    }

    /**
     * @param  string $controllerService
     * @return RestEndpointMetadata|false
     */
    public function fetch($controllerService)
    {
        $config = $this->configResource->fetch(true);
        if (!isset($config['zf-rest'])
            || !isset($config['zf-rest'][$controllerService])
        ) {
            throw new Exception\RuntimeException(sprintf(
                'Could not find REST resource by name of %s',
                $controllerService
            ), 404);
        }

        $restConfig = $config['zf-rest'][$controllerService];

        $restConfig['controllerServiceName'] = $controllerService;
        $restConfig['module']                = $this->module;
        $restConfig['resource_class']        = $restConfig['listener'];
        unset($restConfig['listener']);

        $metadata = new RestEndpointMetadata();
        $metadata->exchangeArray($restConfig);

        $this->getRouteInfo($metadata, $config);
        $this->mergeContentNegotiationConfig($controllerService, $metadata, $config);
        $this->mergeHalConfig($controllerService, $metadata, $config);

        return $metadata;
    }

    /**
     * Fetch all endpoints
     *
     * @return RestEndpointMetadata[]
     */
    public function fetchAll()
    {
        $config = $this->configResource->fetch(true);
        if (!isset($config['zf-rest'])) {
            return array();
        }

        $endpoints = array();
        foreach (array_keys($config['zf-rest']) as $controllerService) {
            $endpoints[] = $this->fetch($controllerService);
        }

        return $endpoints;
    }

    /**
     * Create a new service endpoint using the details provided
     *
     * @param  RestCreationEndpoint $details
     * @return RestEndpointMetadata
     */
    public function createService(RestCreationEndpoint $details)
    {
        $resourceName      = $details->resourceName;
        $controllerService = $this->createControllerServiceName($resourceName);
        $resourceClass     = $this->createResourceClass($resourceName);
        $entityClass       = $this->createEntityClass($resourceName);
        $collectionClass   = $this->createCollectionClass($resourceName);
        $routeName         = $this->createRoute($resourceName, $details->routeMatch, $details->identifierName, $controllerService);
        $this->createRestConfig($details, $controllerService, $resourceClass, $routeName);
        $this->createContentNegotiationConfig($details, $controllerService);
        $this->createHalConfig($details, $entityClass, $collectionClass, $routeName);

        $metadata = new RestEndpointMetadata();
        $metadata->exchangeArray($details->getArrayCopy());
        $metadata->exchangeArray(array(
            'collection_class'        => $collectionClass,
            'controller_service_name' => $controllerService,
            'entity_class'            => $entityClass,
            'module'                  => $this->module,
            'resource_class'          => $resourceClass,
            'route_name'              => $routeName,
        ));

        return $metadata;
    }

    /**
     * Update an existing service
     *
     * @param RestEndpointMetadata $update
     * @return RestEndpointMetadata
     */
    public function updateService(RestEndpointMetadata $update)
    {
        $controllerService = $update->controllerServiceName;

        try {
            $original = $this->fetch($controllerService);
        } catch (Exception\RuntimeException $e) {
            throw new Exception\RuntimeException(sprintf(
                'Cannot update REST endpoint "%s"; not found',
                $controllerService
            ), 404);
        }

        $this->updateRoute($original, $update);
        $this->updateRestConfig($original, $update);
        $this->updateContentNegotiationConfig($original, $update);

        return $this->fetch($controllerService);
    }

    /**
     * Generate the controller service name from the module and resource name
     *
     * @param  string $module
     * @param  string $resourceName
     * @return string
     */
    public function createControllerServiceName($resourceName)
    {
        return sprintf('%s\\Controller\\%s', $this->module, ucfirst($resourceName));
    }

    /**
     * Creates a new resource class based on the specified resource name
     *
     * @param  string $resourceName
     * @return string The name of the newly created class
     */
    public function createResourceClass($resourceName)
    {
        $module  = $this->module;
        $srcPath = $this->getSourcePath();

        $className = sprintf('%sResource', ucfirst($resourceName));
        $classPath = sprintf('%s/%s.php', $srcPath, $className);

        if (file_exists($classPath)) {
            throw new Exception\RuntimeException(sprintf(
                'The resource "%s" already exists',
                $className
            ));
        }

        $view = new ViewModel(array(
            'module'    => $module,
            'classname' => $className,
        ));
        if (!$this->createClassFile($view, 'resource', $classPath)) {
            throw new Exception\RuntimeException(sprintf(
                'Unable to create resource "%s"; unable to write file',
                $className
            ));
        }

        $fullClassName = sprintf('%s\\%s', $module, $className);
        $this->configResource->patch(array(
            'service_manager' => array(
                'invokables' => array(
                    $fullClassName => $fullClassName,
                ),
            ),
        ), true);

        return $fullClassName;
    }

    /**
     * Create an entity class for the resource
     *
     * @param  string $resourceName
     * @return string The name of the newly created entity class
     */
    public function createEntityClass($resourceName)
    {
        $module     = $this->module;
        $srcPath    = $this->getSourcePath();

        $className = ucfirst($resourceName);
        $classPath = sprintf('%s/%s.php', $srcPath, $className);

        if (file_exists($classPath)) {
            throw new Exception\RuntimeException(sprintf(
                'The entity "%s" already exists',
                $className
            ));
        }

        $view = new ViewModel(array(
            'module'    => $module,
            'classname' => $className,
        ));
        if (!$this->createClassFile($view, 'entity', $classPath)) {
            throw new Exception\RuntimeException(sprintf(
                'Unable to create entity "%s"; unable to write file',
                $className
            ));
        }

        $fullClassName = sprintf('%s\\%s', $module, $className);
        return $fullClassName;
    }

    /**
     * Create a collection class for the resource
     *
     * @param  string $resourceName
     * @return string The name of the newly created collection class
     */
    public function createCollectionClass($resourceName)
    {
        $module     = $this->module;
        $srcPath    = $this->getSourcePath();

        $className = sprintf('%sCollection', ucfirst($resourceName));
        $classPath = sprintf('%s/%s.php', $srcPath, $className);

        if (file_exists($classPath)) {
            throw new Exception\RuntimeException(sprintf(
                'The collection "%s" already exists',
                $className
            ));
        }

        $view = new ViewModel(array(
            'module'    => $module,
            'classname' => $className,
        ));
        if (!$this->createClassFile($view, 'collection', $classPath)) {
            throw new Exception\RuntimeException(sprintf(
                'Unable to create entity "%s"; unable to write file',
                $className
            ));
        }

        $fullClassName = sprintf('%s\\%s', $module, $className);
        return $fullClassName;
    }

    /**
     * Create the route configuration
     *
     * @param  string $resourceName
     * @param  string $route
     * @param  string $identifier
     * @param  string $controllerService
     * @return string
     */
    public function createRoute($resourceName, $route, $identifier, $controllerService)
    {
        $filter    = $this->getRouteNameFilter();
        $routeName = sprintf(
            '%s.%s',
            $filter->filter($this->module),
            $filter->filter($resourceName)
        );

        $config = array('router' => array('routes' => array(
            $routeName => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => sprintf('%s[/:%s]', $route, $identifier),
                    'defaults' => array(
                        'controller' => $controllerService,
                    ),
                ),
            ),
        )));
        $this->configResource->patch($config, true);
        return $routeName;
    }

    /**
     * Creates REST configuration
     *
     * @param  RestEndpointMetadata $details
     * @param  string $controllerService
     * @param  string $resourceClass
     * @param  string $routeName
     */
    public function createRestConfig(RestEndpointMetadata $details, $controllerService, $resourceClass, $routeName)
    {
        $config = array('zf-rest' => array(
            $controllerService => array(
                'listener'                   => $resourceClass,
                'route_name'                 => $routeName,
                'identifier_name'            => $details->identifierName,
                'collection_name'            => $details->collectionName,
                'resource_http_options'      => $details->resourceHttpOptions,
                'collection_http_options'    => $details->collectionHttpOptions,
                'collection_query_whitelist' => $details->collectionQueryWhitelist,
                'page_size'                  => $details->pageSize,
                'page_size_param'            => $details->pageSizeParam,
            ),
        ));
        $this->configResource->patch($config, true);
    }

    /**
     * Create content negotiation configuration based on payload and discovered
     * controller service name
     *
     * @param  RestEndpointMetadata $details
     * @param  string $controllerService
     */
    public function createContentNegotiationConfig(RestEndpointMetadata $details, $controllerService)
    {
        $config = array(
            'controllers' => array(
                $controllerService => $details->selector,
            ),
        );
        $whitelist = $details->acceptWhitelist;
        if (!empty($whitelist)) {
            $config['accept-whitelist'] = array($controllerService => $whitelist);
        }
        $whitelist = $details->contentTypeWhitelist;
        if (!empty($whitelist)) {
            $config['content-type-whitelist'] = array($controllerService => $whitelist);
        }
        $config = array('zf-content-negotiation' => $config);
        $this->configResource->patch($config, true);
    }

    /**
     * Create HAL configuration
     *
     * @param  RestEndpointMetadata $details
     * @param  string $entityClass
     * @param  string $collectionClass
     * @param  string $routeName
     */
    public function createHalConfig(RestEndpointMetadata $details, $entityClass, $collectionClass, $routeName)
    {
        $config = array('zf-hal' => array('metadata_map' => array(
            $entityClass => array(
                'identifier_name' => $details->identifierName,
                'route_name'      => $routeName,
            ),
            $collectionClass => array(
                'identifier_name' => $details->identifierName,
                'route_name'      => $routeName,
                'is_collection'   => true,
            ),
        )));
        $this->configResource->patch($config, true);
    }

    /**
     * Update the route for an existing endpoint
     *
     * @param  RestEndpointMetadata $original
     * @param  RestEndpointMetadata $update
     */
    public function updateRoute(RestEndpointMetadata $original, RestEndpointMetadata $update)
    {
        $route = $update->routeMatch;
        if (!$route) {
            return;
        }
        $routeName = $original->routeName;
        $config    = array('router' => array('routes' => array(
            $routeName => array('options' => array(
                'route' => sprintf('%s[/:%s]', $route, $original->identifierName),
            ))
        )));
        $this->configResource->patch($config, true);
    }

    /**
     * Update REST configuration
     *
     * @param  RestEndpointMetadata $original
     * @param  RestEndpointMetadata $update
     */
    public function updateRestConfig(RestEndpointMetadata $original, RestEndpointMetadata $update)
    {
        $patch = array();
        foreach ($this->restScalarUpdateOptions as $property => $configKey) {
            if (!$update->$property) {
                continue;
            }
            $patch[$configKey] = $update->$property;
        }

        if (empty($patch)) {
            goto updateArrayOptions;
        }

        $config = array('zf-rest' => array(
            $original->controllerServiceName => $patch,
        ));
        $this->configResource->patch($config, true);

        updateArrayOptions:

        foreach ($this->restArrayUpdateOptions as $property => $configKey) {
            if (!$update->$property) {
                continue;
            }
            $key = sprintf('zf-rest.%s.%s', $original->controllerServiceName, $configKey);
            $this->configResource->patchKey($key, $update->$property);
        }
    }

    /**
     * Update the content negotiation configuration for the service
     *
     * @param  RestEndpointMetadata $original
     * @param  RestEndpointMetadata $update
     */
    public function updateContentNegotiationConfig(RestEndpointMetadata $original, RestEndpointMetadata $update)
    {
        $baseKey = 'zf-content-negotiation.';
        $service = $original->controllerServiceName;

        if ($update->selector) {
            $key = $baseKey . 'controllers.' . $service;
            $this->configResource->patchKey($key, $update->selector);
        }

        // Array dereferencing is a PITA
        $acceptWhitelist = $update->acceptWhitelist;
        if (is_array($acceptWhitelist)
            && !empty($acceptWhitelist)
        ) {
            $key = $baseKey . 'accept-whitelist.' . $service;
            $this->configResource->patchKey($key, $acceptWhitelist);
        }

        $contentTypeWhitelist = $update->contentTypeWhitelist;
        if (is_array($contentTypeWhitelist)
            && !empty($contentTypeWhitelist)
        ) {
            $key = $baseKey . 'content-type-whitelist.' . $service;
            $this->configResource->patchKey($key, $contentTypeWhitelist);
        }
    }

    /**
     * Create a class file
     *
     * Creates a class file based on the view model passed, the type of resource,
     * and writes it to the path provided.
     *
     * @param  ViewModel $model
     * @param  string $type
     * @param  string $classPath
     * @return bool
     */
    protected function createClassFile(ViewModel $model, $type, $classPath)
    {
        $renderer = $this->getRenderer();
        $template = $this->injectResolver($renderer, $type);
        $model->setTemplate($template);

        if (file_put_contents(
            $classPath,
            '<' . "?php\n" . $renderer->render($model)
        )) {
            return true;
        }

        return false;
    }

    /**
     * Get a renderer instance
     *
     * @return PhpRenderer
     */
    protected function getRenderer()
    {
        if ($this->renderer instanceof PhpRenderer) {
            return $this->renderer;
        }

        $this->renderer = new PhpRenderer();
        return $this->renderer;
    }

    /**
     * Inject the renderer with a resolver
     *
     * Seed the resolver with a template name and path based on the $type passed, and inject it
     * into the renderer.
     *
     * @param  PhpRenderer $renderer
     * @param  string $type
     * @return string Template name
     */
    protected function injectResolver(PhpRenderer $renderer, $type)
    {
        $template = sprintf('code-connected/rest-', $type);
        $path     = sprintf('%s/../../../../view/code-connected/rest-%s.phtml', __DIR__, $type);
        $resolver = new Resolver\TemplateMapResolver(array(
            $template => $path,
        ));
        $renderer->setResolver($resolver);
        return $template;
    }

    /**
     * Get the source path for the module
     *
     * @return string
     */
    protected function getSourcePath()
    {
        if ($this->sourcePath) {
            return $this->sourcePath;
        }

        $sourcePath = sprintf(
            '%s/src/%s',
            $this->modulePath,
            str_replace('\\', '/', $this->module)
        );

        if (!file_exists($sourcePath)) {
            mkdir($sourcePath, 0777, true);
        }

        $this->sourcePath = $sourcePath;
        return $sourcePath;
    }

    /**
     * Retrieve the filter chain for generating the route name
     *
     * @return FilterChain
     */
    protected function getRouteNameFilter()
    {
        if ($this->routeNameFilter instanceof FilterChain) {
            return $this->routeNameFilter;
        }

        $this->routeNameFilter = new FilterChain();
        $this->routeNameFilter->attachByName('Word\CamelCaseToDash')
            ->attachByName('StringToLower');
        return $this->routeNameFilter;
    }

    /**
     * Retrieve route information for a given endpoint based on the configuration available
     *
     * @param  RestEndpointMetadata $metadata
     * @param  array $config
     */
    protected function getRouteInfo(RestEndpointMetadata $metadata, array $config)
    {
        $routeName = $metadata->routeName;
        if (!$routeName
            || !isset($config['router'])
            || !isset($config['router']['routes'])
            || !isset($config['router']['routes'][$routeName])
            || !isset($config['router']['routes'][$routeName]['options'])
            || !isset($config['router']['routes'][$routeName]['options']['route'])
        ) {
            return;
        }
        $metadata->exchangeArray(array(
            'route_match' => $config['router']['routes'][$routeName]['options']['route'],
        ));
    }

    /**
     * Merge the content negotiation configuration for the given controller
     * service into the REST metadata
     *
     * @param  string $controllerServiceName
     * @param  RestEndpointMetadata $metadata
     * @param  array $config
     */
    protected function mergeContentNegotiationConfig($controllerServiceName, RestEndpointMetadata $metadata, array $config)
    {
        if (!isset($config['zf-content-negotiation'])) {
            return;
        }
        $config = $config['zf-content-negotiation'];

        if (isset($config['controllers'])
            && isset($config['controllers'][$controllerServiceName])
        ) {
            $metadata->exchangeArray(array(
                'selector' => $config['controllers'][$controllerServiceName],
            ));
        }

        if (isset($config['accept-whitelist'])
            && isset($config['accept-whitelist'][$controllerServiceName])
        ) {
            $metadata->exchangeArray(array(
                'accept_whitelist' => $config['accept-whitelist'][$controllerServiceName],
            ));
        }

        if (isset($config['content-type-whitelist'])
            && isset($config['content-type-whitelist'][$controllerServiceName])
        ) {
            $metadata->exchangeArray(array(
                'content_type_whitelist' => $config['content-type-whitelist'][$controllerServiceName],
            ));
        }
    }

    /**
     * Merge entity and collection class into metadata, if found
     *
     * @param  string $controllerServiceName
     * @param  RestEndpointMetadata $metadata
     * @param  array $config
     */
    protected function mergeHalConfig($controllerServiceName, RestEndpointMetadata $metadata, array $config)
    {
        if (!isset($config['zf-hal'])
            || !isset($config['zf-hal']['metadata_map'])
        ) {
            return;
        }

        $config = $config['zf-hal']['metadata_map'];

        $entityClass     = $this->deriveEntityClass($controllerServiceName, $metadata);
        $collectionClass = sprintf('%sCollection', $entityClass);
        $merge           = array();

        if (isset($config[$entityClass])) {
            $merge['entity_class'] = $entityClass;
        }

        if (isset($config[$collectionClass])) {
            $merge['collection_class'] = $collectionClass;
        }

        $metadata->exchangeArray($merge);
    }

    /**
     * Derive the name of the entity class from the controller service name
     *
     * @param  string $controllerServiceName
     * @param  RestEndpointMetadata $metadata
     * @return string
     */
    protected function deriveEntityClass($controllerServiceName, RestEndpointMetadata $metadata)
    {
        $module   = ($metadata->module == $this->module) ? $this->module : $metadata->module;
        $resource = str_replace($module . '\\Controller\\', '', $controllerServiceName);
        return sprintf('%s\\%s', $module, $resource);
    }
}
