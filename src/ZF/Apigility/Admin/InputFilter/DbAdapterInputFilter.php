<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter;

use Zend\InputFilter\InputFilter;

class DbAdapterInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name' => 'adapter_name',
        ));
        $this->add(array(
            'name' => 'database',
        ));
        $this->add(array(
            'name' => 'driver',
        ));
        $this->add(array(
            'name' => 'username',
            'required' => false
        ));
        $this->add(array(
            'name' => 'password',
            'required' => false
        ));
        $this->add(array(
            'name' => 'hostname',
            'required' => false
        ));
        $this->add(array(
            'name' => 'port',
            'required' => false
        ));
        $this->add(array(
            'name' => 'charset',
            'required' => false
        ));
    }

}
