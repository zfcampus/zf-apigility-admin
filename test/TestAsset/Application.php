<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\TestAsset;

class Application
{
    protected $services;

    public function setServiceManager($services)
    {
        $this->services = $services;
    }

    public function getServiceManager()
    {
        return $this->services;
    }
}
