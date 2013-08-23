<?php

namespace ZF\ApiFirstAdmin\Model;

use Zend\Filter\FilterChain;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use ZF\ApiFirstAdmin\Exception;
use ZF\Configuration\ConfigResource;
use ZF\Configuration\ModuleUtils;

class CodeConnectedRpc
{
    /**
     * @var ConfigResource
     */
    protected $configResource;

    /**
     * @var FilterChain
     */
    protected $filter;
        
    /**
     * @var string
     */
    protected $module;

    /**
     * @var ModuleUtils
     */
    protected $modules;

    public function __construct($module, ModuleUtils $modules, ConfigResource $config)
    {
        $this->module         = $module;
        $this->modules        = $modules;
        $this->configResource = $config;
    }

    /**
     * Create a controller in the current module named for the given service
     * 
     * @param  string $serviceName 
     * @return stdClass
     */
    public function createController($serviceName)
    {
        $module     = $this->module;
        $modulePath = $this->modules->getModulePath($module);

        $srcPath = sprintf(
            '%s/src/%s/Controller',
            $modulePath,
            str_replace('\\', '/', $module)
        );

        if (!file_exists($srcPath)) {
            mkdir($srcPath, 0777, true);
        }

        $className         = sprintf('%sController', ucfirst($serviceName));
        $classPath         = sprintf('%s/%s.php', $srcPath, $className);
        $controllerService = sprintf('%s\Controller\\%s', $module, ucfirst($serviceName));

        if (file_exists($classPath)) {
            throw new Exception\RuntimeException(sprintf(
                'The controller "%s" already exists',
                $className
            ));
        }
        
        $view = new ViewModel(array(
            'module'      => $module,
            'classname'   => $className,
            'servicename' => lcfirst($serviceName)
        ));

        $resolver = new Resolver\TemplateMapResolver(array(
            'code-connected/rpc-controller' => __DIR__ . '/../../../../view/code-connected/rpc-controller.phtml'
        ));

        $view->setTemplate('code-connected/rpc-controller');
        $renderer = new PhpRenderer();
        $renderer->setResolver($resolver);

        if (!file_put_contents($classPath,
            "<" . "?php\n" . $renderer->render($view))) {
            return false;
        }

        $fullClassName = sprintf('%s\Controller\\%s', $module, $className);
        $this->configResource->patch(array(
            'controllers' => array(
                'invokables' => array(
                    $controllerService => $fullClassName,
                ),
            ),
        ), true);

        return (object) array(
            'class'   => $fullClassName,
            'file'    => $classPath,
            'service' => $controllerService,
        );
    }

    public function createRoute($route, $serviceName, $controllerService = null)
    {
        if (null === $controllerService) {
            $controllerService = sprintf('%s\Controller\\%s', $this->module, $serviceName);
        }

        $routeName = sprintf('%s.%s', $this->normalize($this->module), $this->normalize($serviceName));
        $action    = lcfirst($serviceName);

        $config = array('router' => array('routes' => array(
            $routeName => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => $route,
                    'defaults' => array(
                        'controller' => $controllerService,
                        'action'     => $action,
                    ),
                ),
            ),
        )));

        return $this->configResource->patch($config, true);
    }

    public function createConfiguration()
    {
    }

    protected function normalize($string)
    {
        $filter = $this->getNormalizationFilter();
        return $filter->filter($string);
    }

    protected function getNormalizationFilter()
    {
        if ($this->filter instanceof FilterChain) {
            return $this->filter;
        }
        $this->filter = new FilterChain();
        $this->filter->attachByName('WordCamelCaseToDash')
                     ->attachByName('StringToLower');
        return $this->filter;
    }
}
