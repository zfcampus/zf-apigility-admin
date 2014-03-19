<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use ZF\ContentNegotiation\ViewModel;

class CacheEnabledController extends AbstractActionController
{
    public function cacheEnabledAction()
    {
        $cacheEnabled = false;

        switch (true) {
            case (php_sapi_name() === 'cli-server'):
                // built-in PHP webserver never truly enables opcode caching
                break;
            case (ini_get('opcache.enable')):
                // zf-configuration has opcache rules for invalidating the cache built-in
                break;
            case (ini_get('apc.enabled') && extension_loaded('apc')):
                // APC
                $cacheEnabled = true;
                break;
            case (ini_get('zend_optimizerplus.enable')):
                // Optimizer+
                $cacheEnabled = true;
                break;
            case (ini_get('eaccelerator.enable')):
                // EAccelerator
                $cacheEnabled = true;
                break;
            case (ini_get('xcache.cacher')):
                // XCache
                $cacheEnabled = true;
                break;
            case (ini_get('wincache.ocenabled')):
                // WinCache
                $cacheEnabled = true;
                break;
        }

        $viewModel = new ViewModel(array('cache_enabled' => $cacheEnabled));
        $viewModel->setTerminal(true);
        return $viewModel;
    }
}
