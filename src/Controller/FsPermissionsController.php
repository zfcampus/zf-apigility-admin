<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use ZF\ContentNegotiation\ViewModel;

/**
 * Detect if filesystem permissions will work for the admin api
 */
class FsPermissionsController extends AbstractActionController
{
    /**
     * Path to the root directory
     *
     * @var string
     */
    protected $root;

    /**
     * @var bool
     */
    protected $rootIsWritable;

    public function __construct()
    {
        $this->root = getcwd();
    }

    /**
     * @return ViewModel
     */
    public function fsPermissionsAction()
    {
        $isWritable = $this->configIsWritable() && $this->moduleIsWritable();
        $viewModel = new ViewModel(array(
            'fs_perms' => $isWritable,
            'www_user' => getenv('USER') ?: '',
        ));
        return $viewModel;
    }

    /**
     * Is the application root writable?
     *
     * @return bool
     */
    protected function rootIsWritable()
    {
        if (null !== $this->rootIsWritable) {
            return $this->rootIsWritable;
        }

        $this->rootIsWritable = is_writable($this->root);
        return $this->rootIsWritable;
    }

    /**
     * Are the config and config/autoload directories writable?
     *
     * @return bool
     */
    protected function configIsWritable()
    {
        $dir = $this->root . '/config';
        if (!file_exists($dir)) {
            return $this->rootIsWritable();
        }
        if (!is_writable($dir)) {
            return false;
        }

        $dir .= '/autoload';
        return is_writable($dir);
    }

    /**
     * Is the module directory writable?
     *
     * @return bool
     */
    protected function moduleIsWritable()
    {
        $dir = $this->root . '/module';
        if (!file_exists($dir)) {
            return $this->rootIsWritable();
        }

        return is_writable($dir);
    }
}
