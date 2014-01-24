<?php
return array(
    'Doc\\V1\\Rest\\FooBar\\Controller' => array(
        'description' => 'per rest controller description',
        'entity' => array(
            'GET' => array(
                'description' => 'General description for GET',
                'request' => 'Request for GET doc updated',
            ),
        ),
        'collection' => array(
            'description' => 'General in rest collection',
            'POST' => array(
                'request' => 'Request for POST doc in collection',
                'description' => 'General POST doc in collection',
            ),
        ),
    ),
    'Doc\\V1\\Rpc\\BazBam\\Controller' => array(
        'description' => 'General RPC docs',
        'GET' => array(
            'description' => 'General GET docs',
            'request' => 'General GET docs for request',
            'response' => 'updated description for GET response',
        ),
    ),
);
