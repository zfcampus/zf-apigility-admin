<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter;

use Zend\InputFilter\InputFilter;

class ModuleInputFilter extends InputFilter
{
    public function init()
    {
        $this->add(array(
            'name' => 'name',
            'validators' => array(
                array(
                    'name' => 'ZF\Apigility\Admin\InputFilter\Validator\ModuleNameValidator',
                ),
            ),
            'error_message' => 'The API name must be a valid PHP namespace',
        ));
    }
}
