<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter;

use Zend\InputFilter\InputFilter;
use ZF\Apigility\Admin\InputFilter\Validator\ModuleNameValidator;

class ModuleInputFilter extends InputFilter
{
    public function init()
    {
        $this->add([
            'name' => 'name',
            'validators' => [
                [
                    'name' => ModuleNameValidator::class,
                ],
            ],
            'error_message' => 'The API name must be a valid PHP namespace',
        ]);
    }
}
