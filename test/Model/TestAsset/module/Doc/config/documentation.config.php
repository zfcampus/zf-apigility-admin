<?php
return [
    'Doc\\V1\\Rest\\FooBar\\Controller' => [
        'description' => 'per rest controller description',
        'entity' => [
            'GET' => [
                'description' => 'General description for GET',
                'request' => 'Request for GET doc updated',
            ],
        ],
        'collection' => [
            'description' => 'General in rest collection',
            'POST' => [
                'request' => 'Request for POST doc in collection',
                'description' => 'General POST doc in collection',
            ],
        ],
    ],
    'Doc\\V1\\Rpc\\BazBam\\Controller' => [
        'description' => 'General RPC docs',
        'GET' => [
            'description' => 'General GET docs',
            'request' => 'General GET docs for request',
            'response' => 'updated description for GET response',
        ],
    ],
];
