<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use ZF\ContentNegotiation\ViewModel;
use ZF\Apigility\Admin\Model\DbAutodiscoveryModel;

/**
 * Class DbAutodiscoveryController
 *
 * @package ZF\Apigility\Admin\Controller
 */
class DbAutodiscoveryController extends AbstractActionController
{
    /**
     * @var DbAutodiscoveryModel
     */
    protected $model;

    /**
     * Constructor
     *
     * @param DbAutodiscoveryModel $model
     */
    public function __construct(DbAutodiscoveryModel $model)
    {
        $this->model = $model;
    }

    public function discoverAction()
    {
        $module = $this->params()->fromRoute('name', false);
        $version = $this->params()->fromRoute('version', false);
        $adapter_name = urldecode($this->params()->fromRoute('adapter_name', false));

        $data = $this->model->fetchColumns($module, $version, $adapter_name);

        return new ViewModel(array('payload' => $data));
    }
}
