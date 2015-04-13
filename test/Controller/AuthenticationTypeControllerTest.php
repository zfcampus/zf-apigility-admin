<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Controller;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request;
use Zend\Mvc\Controller\PluginManager as ControllerPluginManager;
use Zend\Mvc\Controller\Plugin\Params;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\SimpleRouteStack;
use ZF\ContentNegotiation\ControllerPlugin\BodyParams;
use ZF\ContentNegotiation\ControllerPlugin\BodyParam;
use ZF\Apigility\Admin\Controller\AuthenticationTypeController;
use ZF\MvcAuth\Authentication\DefaultAuthenticationListener as AuthListener;
use ZF\Apigility\Admin\Model\AuthenticationModel;
use ZF\Configuration\ConfigResource;
use Zend\Config\Writer\PhpArray as ConfigWriter;
use ZF\ContentNegotiation\ParameterDataContainer;

class AuthenticationTypeControllerTest extends TestCase
{
    public function setUp()
    {
        $this->globalFile = __DIR__ . '/TestAsset/Auth2/config/autoload/global.php';
        $this->localFile  = __DIR__ . '/TestAsset/Auth2/config/autoload/local.php';
        copy($this->globalFile . '.dist', $this->globalFile);
        copy($this->localFile . '.dist', $this->localFile);

        $moduleModel = $this->getMockBuilder('ZF\Apigility\Admin\Model\ModuleModel')
                            ->disableOriginalConstructor()
                            ->getMock();

        $model = $this->getAuthModel($this->globalFile, $this->localFile, $moduleModel);

        $this->controller = $this->getController($model, $this->localFile, $this->globalFile);

        $this->routeMatch = new RouteMatch(array());
        $this->routeMatch->setMatchedRouteName('zf-apigility/api/authentication-type');
        $this->event = new MvcEvent();
        $this->event->setRouteMatch($this->routeMatch);

        $this->controller->setEvent($this->event);
    }

    protected function getAuthModel($globalFile, $localFile, $moduleModel)
    {
        $writer = new ConfigWriter();
        $global = new ConfigResource(require $globalFile, $globalFile, $writer);
        $local  = new ConfigResource(require $localFile, $localFile, $writer);

        return new AuthenticationModel($global, $local, $moduleModel);
    }

    protected function getController($model, $localFile, $globalFile)
    {
        $authListener = new AuthListener();
        $config = require $localFile;
        if (isset($config['zf-oauth2']['db'])) {
            $authListener->addAuthenticationTypes($config['zf-oauth2']['db']);
        } elseif (isset($config['zf-oauth2']['mongo'])) {
            $authListener->addAuthenticationTypes($config['zf-oauth2']['mongo']);
        } else {
            if (!isset($config['zf-mvc-auth']['authentication'])) {
                $config = require $globalFile;
            }
            $authListener->addAuthenticationTypes($config['zf-mvc-auth']['authentication']);
        }
        return new AuthenticationTypeController($model, $authListener);
    }

    public function tearDown()
    {
        unlink($this->localFile);
        unlink($this->globalFile);
    }

    public function invalidRequestMethods()
    {
        return array(
            array('post', 'put', 'patch', 'delete')
        );
    }

    /**
     * @dataProvider invalidRequestMethods
     */
    public function testProcessWithInvalidRequestMethodReturnsApiProblemModel($method)
    {
        $request = new Request();
        $request->setMethod($method);
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.apigility.v2+json');
        $this->controller->setRequest($request);

        $result = $this->controller->authTypeAction();
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblemResponse', $result);
        $apiProblem = $result->getApiProblem();
        $this->assertEquals(405, $apiProblem->status);
    }


    public function testGetAuthenticationRequest()
    {
        $request = new Request();
        $request->setMethod('get');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.apigility.v2+json');
        $this->controller->setRequest($request);

        $result = $this->controller->authTypeAction();

        $this->assertInstanceOf('ZF\ContentNegotiation\ViewModel', $result);
        $config = require $this->localFile;
        $adapters = array_keys($config['zf-mvc-auth']['authentication']['adapters']);
        $this->assertEquals($adapters, $result->getVariable('auth-types'));
    }

    public function getOldAuthConfig()
    {
        return array(
            array(
                array(
                    'zf-mvc-auth' => array(
                        'authentication' => array(
                            'http' => array(
                                'accept_schemes' => array('basic'),
                                'realm' => 'My Web Site'
                            )
                        )
                    )
                ),
                array(
                    'zf-mvc-auth' => array(
                        'authentication' => array(
                            'http' => array(
                                'htpasswd' => __DIR__ . '/TestAsset/Auth2/config/autoload/htpasswd'
                            )
                        )
                    )
                )
            ),
            array(
                array(
                    'zf-mvc-auth' => array(
                        'authentication' => array(
                            'http' => array(
                                'accept_schemes' => array('digest'),
                                'realm' => 'My Web Site',
                                'domain_digest' => 'domain.com',
                                'nonce_timeout' => 3600
                            )
                        )
                    )
                ),
                array(
                    'zf-mvc-auth' => array(
                        'authentication' => array(
                            'http' => array(
                                'htpdigest' => __DIR__ . '/TestAsset/Auth2/config/autoload/htdigest'
                            )
                        )
                    )
                )
            ),
            array(
                array(
                    'router' => array(
                        'routes' => array(
                            'oauth' => array(
                                'options' => array(
                                    'route' => '/oauth'
                                )
                            )
                        )
                    )
                ),
                array(
                    'zf-oauth2' => array(
                        'storage' => 'ZF\\OAuth2\\Adapter\\PdoAdapter',
                        'db' => array(
                            'dsn_type'  => 'PDO',
                            'dsn'       => 'sqlite:/' . __DIR__ . '/TestAsset/Auth2/config/autoload/db.sqlite',
                            'username'  => null,
                            'password'  => null
                        )
                    )
                )
            ),
            array(
                array(
                    'router' => array(
                        'routes' => array(
                            'oauth' => array(
                                'options' => array(
                                    'route' => '/oauth'
                                )
                            )
                        )
                    )
                ),
                array(
                    'zf-oauth2' => array(
                        'storage' => 'ZF\\OAuth2\\Adapter\\MongoAdapter',
                        'mongo' => array(
                            'dsn_type'     => 'Mongo',
                            'dsn'          => 'mongodb://localhost',
                            'database'     => 'zf-apigility-admin-test',
                            'locator_name' => 'MongoDB'
                        )
                    )
                )
            ),
        );
    }

    /**
     * @dataProvider getOldAuthConfig
     */
    public function testGetAuthenticationWithOldConfiguration($global, $local)
    {
        file_put_contents($this->globalFile, '<?php return '. var_export($global, true) . ';');
        file_put_contents($this->localFile, '<?php return '. var_export($local, true) . ';');

        $moduleEntity = $this->getMockBuilder('ZF\Apigility\Admin\Model\ModuleEntity')
                             ->disableOriginalConstructor()
                             ->getMock();

        $moduleEntity->expects($this->any())
                     ->method('getName')
                     ->will($this->returnValue('Foo'));

        $moduleEntity->expects($this->any())
                     ->method('getVersions')
                     ->will($this->returnValue(array(1,2)));

        $moduleModel = $this->getMockBuilder('ZF\Apigility\Admin\Model\ModuleModel')
                            ->disableOriginalConstructor()
                            ->getMock();

        $moduleModel->expects($this->any())
                    ->method('getModules')
                    ->will($this->returnValue(array('Foo' => $moduleEntity)));

        $model = $this->getAuthModel($this->globalFile, $this->localFile, $moduleModel);

        $controller = $this->getController($model, $this->localFile, $this->globalFile);

        $request = new Request();
        $request->setMethod('get');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.apigility.v2+json');
        $controller->setRequest($request);

        $routeMatch = new RouteMatch(array());
        $routeMatch->setMatchedRouteName('zf-apigility/api/authentication-type');
        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);
        $controller->setEvent($event);

        $result = $controller->authTypeAction();

        $this->assertInstanceOf('ZF\ContentNegotiation\ViewModel', $result);
        $config = require $this->localFile;
        $adapters = array_keys($config['zf-mvc-auth']['authentication']['adapters']);
        $this->assertEquals($adapters, $result->getVariable('auth-types'));
    }
}
