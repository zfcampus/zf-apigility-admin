<?php
return array(
    'input_filter_specs' => array(
        'InputFilter\V1\Rest\Foo\Validator' => array(
            'foo' => array(
                'name' => 'foo',
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'options' => array(
                            'type' => 127,
                        ),
                    ),
                    array('name' => 'Digits'),
                ),
            ),
        ),
    ),
    'zf-content-validation' => array(
        'InputFilter\V1\Rest\Foo\Controller' => array(
            'input_filter' => 'InputFilter\V1\Rest\Foo\Validator',
        ),
    ),
    'zf-rest' => array(
        'InputFilter\V1\Rest\Foo\Controller' => array(),
        'InputFilter\V1\Rest\Bar\Controller' => array(),
    ),
);
