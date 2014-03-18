<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter;

use Zend\InputFilter\InputFilter;

class VersionInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name' => 'module',
            'validators' => array(
                array('name' => 'ZF\Apigility\Admin\InputFilter\Validator\ModuleNameValidator'),
            ),
        ));
        $this->add(array(
            'name' => 'version',
            'validators' => array(
                array('name' => 'Zend\Validator\Digits')
            )
        ));
    }
}
