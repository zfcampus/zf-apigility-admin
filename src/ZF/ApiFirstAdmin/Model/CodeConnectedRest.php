<?php

namespace ZF\ApiFirstAdmin\Model;

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
        $controllerService = $this->createControllerServiceName($resourceName);
        $resourceClass     = $this->createResourceClass($resourceName);
        $entityClass       = $this->createEntityClass($resourceName);
        $collectionClass   = $this->createCollectionClass($resourceName);
        $routeName         = $this->createRoute($details->route, $controllerService, $details->identifierName);
        $this->createRestConfig($details, $resourceClass, $routeName);
        $this->createContentNegotiationConfig($details);
        $this->createHalConfig($details, $entityClass, $collectionClass);

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
        return sprintf('%s\\Controller\\%s', $this->module, $resourceName);
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
}
