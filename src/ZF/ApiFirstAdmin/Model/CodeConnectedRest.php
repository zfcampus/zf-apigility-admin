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

    public function createResourceClass($resourceName)
    {
        $module     = $this->module;
        $modulePath = $this->modules->getModulePath($module);

        $srcPath = sprintf(
            '%s/src/%s',
            $modulePath,
            str_replace('\\', '/', $module)
        );

        if (!file_exists($srcPath)) {
            mkdir($srcPath, 0777, true);
        }

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
        $view->setTemplate('code-connected/rest-resource');

        $resolver = new Resolver\TemplateMapResolver(array(
            'code-connected/rest-resource' => __DIR__ . '/../../../../view/code-connected/rest-resource.phtml'
        ));
        $renderer = new PhpRenderer();
        $renderer->setResolver($resolver);

        if (!file_put_contents(
            $classPath,
            '<' . "?php\n" . $renderer->render($view)
        )) {
            return false;
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
}
