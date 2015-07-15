<?php
return [
    'input_filter_specs' => [
        'InputFilter\V1\Rest\Foo\Validator' => [
            'foo' => [
                'name' => 'foo',
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'type' => 127,
                        ],
                    ],
                    ['name' => 'Digits'],
                ],
            ],
        ],
    ],
    'zf-content-validation' => [
        'InputFilter\V1\Rest\Foo\Controller' => [
            'input_filter' => 'InputFilter\V1\Rest\Foo\Validator',
        ],
    ],
    'zf-rest' => [
        'InputFilter\V1\Rest\Foo\Controller' => [],
        'InputFilter\V1\Rest\Bar\Controller' => [],
    ],
];
