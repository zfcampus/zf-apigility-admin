<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use ZF\Configuration\ConfigResource;

class ConfigController extends AbstractConfigController
{
    protected $config;

    public function __construct(ConfigResource $config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }
}
