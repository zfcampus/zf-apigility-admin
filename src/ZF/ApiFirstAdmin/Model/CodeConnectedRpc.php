<?php

namespace ZF\ApiFirstAdmin\Model;

use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use ZF\Configuration\ConfigResource;

class CodeConnectedRpc
{
    /**
     * @var string
     */
    protected $module;
    /**
     * @var ConfigResource
     */
    protected $configResource;
        
    public function __construct($module, ConfigResource $config)
    {
        $this->module = $module;
        $this->configResource = $config;
    }

    public function createController($serviceName, $path = '.')
    {
        $module = ucfirst($this->module);
        
        $modulePath = sprintf('%s/module/%s', $path, $module);
        if (!file_exists($modulePath)) {
            return false;
        }

        if (!file_exists("$modulePath/src/$module/Controller")) {
            mkdir ("$modulePath/src/$module/Controller");
        }

        $className = sprintf('%sController', ucfirst($serviceName));

        if (file_exists("$modulePath/src/$module/Controller/$className.php")) {
            throw new Exception\RuntimeException(
                "The controller $controllerName already exists"
            );
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

        if (!file_put_contents("$modulePath/src/$module/Controller/$className.php",
            "<" . "?php\n" . $renderer->render($view))) {
            return false;
        }

        $config = $this->configurationResource->fetch();
        var_dump($config);

        return true;

    }

    public function createRoute()
    {
    }

    public function createConfiguration()
    {
    }
}

