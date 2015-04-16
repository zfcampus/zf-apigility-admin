<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use ZF\Apigility\Admin\Model\AuthenticationEntity;
use ZF\Apigility\Admin\Model\AuthenticationModel;
use ZF\Apigility\Admin\Model\ContentNegotiationModel;
use ZF\Apigility\Admin\Model\DbAdapterModel;
use ZF\Apigility\Admin\Model\DoctrineAdapterModel;
use ZF\Apigility\Admin\Model\ModuleModel;
use ZF\Apigility\Admin\Model\RestServiceModelFactory;
use ZF\Apigility\Admin\Model\RpcServiceModelFactory;
use ZF\ContentNegotiation\ViewModel;
use ZF\Hal\Entity;
use ZF\Hal\Collection;
use ZF\Hal\Link\Link;

class DashboardController extends AbstractActionController
{
    protected $authentication;

    protected $contentNegotiation;

    protected $dbAdapters;

    protected $doctrineAdapters;

    protected $modules;

    protected $restServicesFactory;

    protected $rpcServicesFactory;

    public function __construct(
        AuthenticationModel $authentication,
        ContentNegotiationModel $contentNegotiation,
        DbAdapterModel $dbAdapters,
        ModuleModel $modules,
        RestServiceModelFactory $restServicesFactory,
        RpcServiceModelFactory $rpcServicesFactory
    ) {
        $this->authentication      = $authentication;
        $this->contentNegotiation  = $contentNegotiation;
        $this->dbAdapters          = $dbAdapters;
        $this->modules             = $modules;
        $this->restServicesFactory = $restServicesFactory;
        $this->rpcServicesFactory  = $rpcServicesFactory;
    }

    public function dashboardAction()
    {
        $dbAdapters = new Collection($this->dbAdapters->fetchAll());
        $dbAdapters->setCollectionRoute('zf-apigility/api/db-adapter');

        $modules = $this->modules->getModules();
        $map     = function ($value) {
            return $value->serviceName;
        };
        foreach ($modules as $module) {
            $name    = $module->getName();
            $version = $module->getLatestVersion();

            $rest = $this->restServicesFactory->factory($name)->fetchAll($version);
            $rest = array_map($map, $rest);

            $rpc = $this->rpcServicesFactory->factory($name)->fetchAll($version);
            $rpc = array_map($map, $rpc);

            $module->exchangeArray(array(
                'rest' => $rest,
                'rpc'  => $rpc,
            ));
        }

        $modulesCollection = new Collection($modules);
        $modulesCollection->setCollectionRoute('zf-apigility/api/module');

        $dashboard = array(
            'db_adapter'       => $dbAdapters,
            'module'           => $modulesCollection,
        );

        $entity = new Entity($dashboard, 'dashboard');
        $links  = $entity->getLinks();
        $links->add(Link::factory(array(
            'rel' => 'self',
            'route' => array(
                'name' => 'zf-apigility/api/dashboard',
            ),
        )));

        return new ViewModel(array('payload' => $entity));
    }

    public function settingsDashboardAction()
    {
        $authentication = $this->authentication->fetch();
        if ($authentication) {
            $authenticationEntity = $authentication;
            $authentication = new Entity($authentication, null);
            $authentication->getLinks()->add(Link::factory(array(
                'rel' => 'self',
                'route' => $this->getRouteForEntity($authenticationEntity),
            )));
        }

        $dbAdapters = new Collection($this->dbAdapters->fetchAll());
        $dbAdapters->setCollectionRoute('zf-apigility/api/db-adapter');

        $contentNegotiation = new Collection($this->contentNegotiation->fetchAll());
        $contentNegotiation->setCollectionRoute('zf-apigility/api/content-negotiation');

        $dashboard = array(
            'authentication'      => $authentication,
            'content_negotiation' => $contentNegotiation,
            'db_adapter'          => $dbAdapters,
        );

        $entity = new Entity($dashboard, 'settings-dashboard');
        $links  = $entity->getLinks();
        $links->add(Link::factory(array(
            'rel' => 'self',
            'route' => array(
                'name' => 'zf-apigility/api/settings-dashboard',
            ),
        )));

        return new ViewModel(array('payload' => $entity));
    }

    /**
     * Determine the route to use for a given entity
     *
     * Copied from AuthenticationController
     *
     * @param  AuthenticationEntity $entity
     * @return string
     */
    protected function getRouteForEntity(AuthenticationEntity $entity)
    {
        $baseRoute = 'zf-apigility/api/authentication';

        if ($entity->isBasic()) {
            return $baseRoute . '/http-basic';
        }

        if ($entity->isDigest()) {
            return $baseRoute . '/http-digest';
        }

        if ($entity->isOAuth2()) {
            return $baseRoute . '/oauth2';
        }

        return $baseRoute;
    }
}
