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

        $className         = sprintf("%s\\Rpc\\%s\\%sController", $this->module, $serviceName, $serviceName);
        $fileName          = sprintf("%s/TestAsset/module/%s/src/%s/Rpc/%s/%sController.php", __DIR__, $this->module, $this->module, $serviceName, $serviceName);
        $controllerService = sprintf("%s\\Rpc\\%s\\Controller", $this->module, $serviceName);

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
        $result = $this->codeRpc->createRoute('/foo_conf/hello_world', 'HelloWorld', 'FooConf\Rpc\HelloWorld\Controller');
        $this->assertEquals('foo-conf.rpc.hello-world', $result);

        $configFile = $this->modules->getModuleConfigPath($this->module);
        $config     = include $configFile;
        $expected   = array(
            'router' => array('routes' => array(
                'foo-conf.rpc.hello-world' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/foo_conf/hello_world',
                        'defaults' => array(
                            'controller' => 'FooConf\Rpc\HelloWorld\Controller',
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
            'controller_service' => 'FooConf\Rpc\HelloWorld\Controller',
        );
    }

    public function testCanCreateRpcConfiguration()
    {
        $result = $this->codeRpc->createRpcConfig('FooConf\Rpc\HelloWorld\Controller', 'foo-conf.rpc.hello-world', array('GET', 'PATCH'));
        $expected = array(
            'zf-rpc' => array(
                'FooConf\Rpc\HelloWorld\Controller' => array(
                    'http_methods' => array('GET', 'PATCH'),
                    'route_name'   => 'foo-conf.rpc.hello-world',
                ),
            ),
        );
        $this->assertEquals($expected, $result);

        $configFile = $this->modules->getModuleConfigPath($this->module);
        $config     = include $configFile;
        $this->assertEquals($expected, $config);

        return (object) array(
            'controller_service' => 'FooConf\Rpc\HelloWorld\Controller',
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
        $result = $this->codeRpc->createSelectorConfig('FooConf\Rpc\HelloWorld\Controller', $selector);
        $expected = array(
            'zf-content-negotiation' => array(
                'controllers' => array(
                    'FooConf\Rpc\HelloWorld\Controller' => $expected,
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
            'controller_service' => 'FooConf\Rpc\HelloWorld\Controller',
        );
    }

    public function testCanGenerateAllArtifactsAtOnceViaCreateService()
    {
        $serviceName = 'HelloWorld';
        $route       = '/foo_conf/hello/world';
        $httpMethods = array('GET', 'PATCH');
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        $this->assertInstanceOf('ZF\ApiFirstAdmin\Model\RpcEndpointEntity', $result);

        $configFile = $this->modules->getModuleConfigPath($this->module);
        $expected   = array(
            'controllers' => array('invokables' => array(
                'FooConf\Rpc\HelloWorld\Controller' => 'FooConf\Rpc\HelloWorld\HelloWorldController',
            )),
            'router' => array('routes' => array(
                'foo-conf.rpc.hello-world' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/foo_conf/hello/world',
                        'defaults' => array(
                            'controller' => 'FooConf\Rpc\HelloWorld\Controller',
                            'action' => 'helloWorld',
                        ),
                    ),
                ),
            )),
            'zf-rpc' => array(
                'FooConf\Rpc\HelloWorld\Controller' => array(
                    'http_methods' => array('GET', 'PATCH'),
                    'route_name'   => 'foo-conf.rpc.hello-world',
                ),
            ),
            'zf-content-negotiation' => array(
                'controllers' => array(
                    'FooConf\Rpc\HelloWorld\Controller' => $selector,
                ),
            ),
        );
        $config = include $configFile;
        $this->assertEquals($expected, $config);

        $class     = 'FooConf\Rpc\HelloWorld\HelloWorldController';
        $classFile = sprintf('%s/TestAsset/module/FooConf/src/FooConf/Rpc/HelloWorld/HelloWorldController.php', __DIR__);
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
        $httpMethods = array('GET', 'PATCH');
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
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
        $this->assertEquals($methods, $config['zf-rpc'][$configData->controller_service]['http_methods']);
    }

    public function testCanUpdateContentNegotiationSelector()
    {
        $configFile = $this->modules->getModuleConfigPath($this->module);
        $this->writer->toFile($configFile, array(
            'zf-content-negotiation' => array(
                'controllers' => array(
                    'FooConf\Rpc\HelloWorld\Controller' => 'Json',
                ),
            ),
        ));
        $this->assertTrue($this->codeRpc->updateSelector('FooConf\Rpc\HelloWorld\Controller', 'MyCustomSelector'));
        $config = include $configFile;
        $this->assertEquals('MyCustomSelector', $config['zf-content-negotiation']['controllers']['FooConf\Rpc\HelloWorld\Controller']);
    }

    public function testCanUpdateContentNegotiationWhitelists()
    {
        $configFile = $this->modules->getModuleConfigPath($this->module);
        $this->writer->toFile($configFile, array(
            'zf-content-negotiation' => array(
                'accept-whitelist' => array(
                    'FooConf\Rpc\HelloWorld\Controller' => array(
                        'application/json',
                        'application/*+json',
                    ),
                ),
                'content-type-whitelist' => array(
                    'FooConf\Rpc\HelloWorld\Controller' => array(
                        'application/json',
                    ),
                ),
            ),
        ));
        $this->assertTrue($this->codeRpc->updateContentNegotiationWhitelist('FooConf\Rpc\HelloWorld\Controller', 'accept', array('application/xml', 'application/*+xml')));
        $this->assertTrue($this->codeRpc->updateContentNegotiationWhitelist('FooConf\Rpc\HelloWorld\Controller', 'content-type', array('application/xml')));
        $config = include $configFile;
        $this->assertEquals(array(
            'application/xml',
            'application/*+xml',
        ), $config['zf-content-negotiation']['accept-whitelist']['FooConf\Rpc\HelloWorld\Controller']);
        $this->assertEquals(array(
            'application/xml',
        ), $config['zf-content-negotiation']['content-type-whitelist']['FooConf\Rpc\HelloWorld\Controller']);
    }
}
