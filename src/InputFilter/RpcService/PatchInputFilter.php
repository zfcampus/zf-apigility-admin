<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\RpcService;

class PatchInputFilter extends PostInputFilter
{
    public function init()
    {
        parent::init();

        $this->add([
            'name' => 'controller_class',
            'required' => true,
            'error_message' => 'The Controller Class must be a valid, fully qualified, PHP class name',
        ]);

        $this->add([
            'name' => 'accept_whitelist',
            'validators' => [
                ['name' => 'ZF\Apigility\Admin\InputFilter\Validator\MediaTypeArrayValidator']
            ],
            'error_message' => 'The Accept Whitelist must be an array of valid media type expressions',
        ]);
        $this->add([
            'name' => 'content_type_whitelist',
            'validators' => [
                ['name' => 'ZF\Apigility\Admin\InputFilter\Validator\MediaTypeArrayValidator']
            ],
            'error_message' => 'The Content-Type Whitelist must be an array of valid media type expressions',
        ]);
        $this->add([
            'name' => 'selector',
            'required' => false,
            'allow_empty' => true,
            'error_message' => 'The Content Negotiation Selector must be a valid,'
                . ' defined zf-content-negotiation selector name',
        ]);

        $this->add([
            'name' => 'http_methods',
            'validators' => [
                ['name' => 'ZF\Apigility\Admin\InputFilter\Validator\HttpMethodArrayValidator']
            ],
            'error_message' => 'The HTTP Methods must be an array of valid HTTP method names',
        ]);
    }
}
