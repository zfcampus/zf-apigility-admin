<?php

namespace ZFTest\ApiFirstAdmin\Model;

use FooConf;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;
use ZF\ApiFirstAdmin\Model\RpcEndpointModel;
use ZF\Configuration\ResourceFactory;
use ZF\Configuration\ModuleUtils;
use Zend\Config\Writer\PhpArray;

require_once __DIR__ . '/TestAsset/module/FooConf/Module.php';

class RpcEndpointModelTest extends TestCase
{
    /**
     * Remove a directory even if not empty (recursive delete)
     *
     * @param  string $dir
     * @return boolean
     */
    protected function removeDir($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = "$dir/$file";
            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                unlink($path);
            }
        }
        return rmdir($dir);
    }

    protected function cleanUpAssets()
    {
        $basePath   = sprintf('%s/TestAsset/module/%s', __DIR__, $this->module);
        $configPath = $basePath . '/config';
        $srcPath    = $basePath . '/src';
        if (is_dir($srcPath)) {
            $this->removeDir($srcPath);
        }
        copy($configPath . '/module.config.php.dist', $configPath . '/module.config.php');
    }

    public function setUp()
    {
        $this->module = 'FooConf';
        $this->cleanUpAssets();

        $modules = array(
            'FooConf' => new FooConf\Module()
        );

        $this->moduleManager = $this->getMockBuilder('Zend\ModuleManager\ModuleManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $this->writer   = new PhpArray();
        $this->modules  = new ModuleUtils($this->moduleManager);
        $this->resource = new ResourceFactory($this->modules, $this->writer);
        $this->codeRpc  = new RpcEndpointModel($this->module, $this->modules, $this->resource->factory('FooConf'));
    }

    public function tearDown()
    {
        $this->cleanUpAssets();
    }

    public function testCreateControllerRpc()
    {
        $serviceName = 'Bar';
        $moduleSrcPath = sprintf('%s/TestAsset/module/%s/src/%s', __DIR__, $this->module, $this->module);
        if (!is_dir($moduleSrcPath)) {
            mkdir($moduleSrcPath, 0777, true);
        }

        $result = $this->codeRpc->createController($serviceName);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertObjectHasAttribute('class', $result);
        $this->assertObjectHasAttribute('file', $result);
        $this->assertObjectHasAttribute('service', $result);

        $className         = sprintf("%s\Controller\%sController", $this->module, $serviceName);
        $fileName          = sprintf("%s/TestAsset/module/%s/src/%s/Controller/%sController.php", __DIR__, $this->module, $this->module, $serviceName);
        $controllerService = sprintf("%s\Controller\%s", $this->module, $serviceName);

        $this->assertEquals($className, $result->class);
        $this->assertEquals($fileName, $result->file);
        $this->assertEquals($controllerService, $result->service);

        require_once $fileName;
        $controllerClass = new ReflectionClass($className);
        $this->assertTrue($controllerClass->isSubclassOf('Zend\Mvc\Controller\AbstractActionController'));

        $actionMethodName = lcfirst($serviceName) . 'Action';
        $this->assertTrue($controllerClass->hasMethod($actionMethodName), 'Expected ' . $actionMethodName . "; class:\n" . file_get_contents($fileName));

        $configFile = $this->modules->getModuleConfigPath($this->module);
        $config     = include $configFile;
        $expected = array(
            'controllers' => array('invokables' => array(
                $controllerService => $className,
            )),
        );
        $this->assertEquals($expected, $config);
    }

    public function testCanCreateRouteConfiguration()
    {
        $result = $this->codeRpc->createRoute('/foo_conf/hello_world', 'HelloWorld', 'FooConf\Controller\HelloWorld');
        $this->assertEquals('foo-conf.hello-world', $result);

        $configFile = $this->modules->getModuleConfigPath($this->module);
        $config     = include $configFile;
        $expected   = array(
            'router' => array('routes' => array(
                'foo-conf.hello-world' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/foo_conf/hello_world',
                        'defaults' => array(
                            'controller' => 'FooConf\Controller\HelloWorld',
                            'action' => 'helloWorld',
                        ),
                    ),
                ),
            )),
        );
        $this->assertEquals($expected, $config);
        return (object) array(
            'config'             => $config,
            'config_file'        => $configFile,
            'controller_service' => 'FooConf\Controller\HelloWorld',
        );
    }

    public function testCanCreateRpcConfiguration()
    {
        $result = $this->codeRpc->createRpcConfig('FooConf\Controller\HelloWorld', 'foo-conf.hello-world', array('GET', 'PATCH'));
        $expected = array(
            'zf-rpc' => array(
                'FooConf\Controller\HelloWorld' => array(
                    'http_options' => array('GET', 'PATCH'),
                    'route_name'   => 'foo-conf.hello-world',
                ),
            ),
        );
        $this->assertEquals($expected, $result);

        $configFile = $this->modules->getModuleConfigPath($this->module);
        $config     = include $configFile;
        $this->assertEquals($expected, $config);

        return (object) array(
            'controller_service' => 'FooConf\Controller\HelloWorld',
            'config'             => $config,
            'config_file'        => $configFile,
        );
    }

    public function contentNegotiationSelectors()
    {
        return array(
            'defaults' => array(null, 'Json'),
            'HalJson' => array('HalJson', 'HalJson'),
        );
    }

    /**
     * @dataProvider contentNegotiationSelectors
     */
    public function testCanCreateContentNegotiationSelectorConfiguration($selector, $expected)
    {
        $result = $this->codeRpc->createSelectorConfig('FooConf\Controller\HelloWorld', $selector);
        $expected = array(
            'zf-content-negotiation' => array(
                'controllers' => array(
                    'FooConf\Controller\HelloWorld' => $expected,
                ),
            ),
        );
        $this->assertEquals($expected, $result);

        $configFile = $this->modules->getModuleConfigPath($this->module);
        $config     = include $configFile;
        $this->assertEquals($expected, $config);

        return (object) array(
            'config'             => $config,
            'config_file'        => $configFile,
            'controller_service' => 'FooConf\Controller\HelloWorld',
        );
    }

    public function testCanGenerateAllArtifactsAtOnceViaCreateService()
    {
        $serviceName = 'HelloWorld';
        $route       = '/foo_conf/hello/world';
        $httpOptions = array('GET', 'PATCH');
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpOptions, $selector);
        $this->assertInstanceOf('ZF\ApiFirstAdmin\Model\RpcEndpoint', $result);

        $configFile = $this->modules->getModuleConfigPath($this->module);
        $expected   = array(
            'controllers' => array('invokables' => array(
                'FooConf\Controller\HelloWorld' => 'FooConf\Controller\HelloWorldController',
            )),
            'router' => array('routes' => array(
                'foo-conf.hello-world' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/foo_conf/hello/world',
                        'defaults' => array(
                            'controller' => 'FooConf\Controller\HelloWorld',
                            'action' => 'helloWorld',
                        ),
                    ),
                ),
            )),
            'zf-rpc' => array(
                'FooConf\Controller\HelloWorld' => array(
                    'http_options' => array('GET', 'PATCH'),
                    'route_name'   => 'foo-conf.hello-world',
                ),
            ),
            'zf-content-negotiation' => array(
                'controllers' => array(
                    'FooConf\Controller\HelloWorld' => $selector,
                ),
            ),
        );
        $config = include $configFile;
        $this->assertEquals($expected, $config);

        $class     = 'FooConf\Controller\HelloWorldController';
        $classFile = sprintf('%s/TestAsset/module/FooConf/src/FooConf/Controller/HelloWorldController.php', __DIR__);
        $this->assertTrue(file_exists($classFile));
        require_once $classFile;
        $controllerClass = new ReflectionClass($class);
        $this->assertTrue($controllerClass->isSubclassOf('Zend\Mvc\Controller\AbstractActionController'));

        $actionMethodName = lcfirst($serviceName) . 'Action';
        $this->assertTrue($controllerClass->hasMethod($actionMethodName), 'Expected ' . $actionMethodName . "; class:\n" . file_get_contents($classFile));

        return (object) array(
            'rpc_endpoint' => $result->getArrayCopy(),
            'config_file'  => $configFile,
            'config'       => $config,
        );
    }

    /**
     * @depends testCanGenerateAllArtifactsAtOnceViaCreateService
     */
    public function testCanUpdateRoute($data)
    {
        // State is lost in between tests; re-seed the service
        $serviceName = 'HelloWorld';
        $route       = '/foo_conf/hello/world';
        $httpOptions = array('GET', 'PATCH');
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpOptions, $selector);
        $endpoint    = $result->getArrayCopy();

        // and now do the actual work for the test
        $this->assertTrue($this->codeRpc->updateRoute($endpoint['controller_service_name'], '/api/hello/world'));
        $configFile = $this->modules->getModuleConfigPath($this->module);
        $config     = include $configFile;
        $this->assertEquals('/api/hello/world', $config['router']['routes'][$endpoint['route_name']]['options']['route']);
    }

    /**
     * @depends testCanCreateRpcConfiguration
     */
    public function testCanUpdateHttpMethods($configData)
    {
        $methods = array('GET', 'PUT', 'DELETE');
        $this->writer->toFile($configData->config_file, $configData->config);
        $this->assertTrue($this->codeRpc->updateHttpMethods($configData->controller_service, $methods));
        $config = include $configData->config_file;
        $this->assertEquals($methods, $config['zf-rpc'][$configData->controller_service]['http_options']);
    }

    public function testCanUpdateContentNegotiationSelector()
    {
        $configFile = $this->modules->getModuleConfigPath($this->module);
        $this->writer->toFile($configFile, array(
            'zf-content-negotiation' => array(
                'controllers' => array(
                    'FooConf\Controller\HelloWorld' => 'Json',
                ),
            ),
        ));
        $this->assertTrue($this->codeRpc->updateSelector('FooConf\Controller\HelloWorld', 'MyCustomSelector'));
        $config = include $configFile;
        $this->assertEquals('MyCustomSelector', $config['zf-content-negotiation']['controllers']['FooConf\Controller\HelloWorld']);
    }
}
