<?php

namespace Test\Foo;

use ZF\Apigility\Provider\ApigilityProviderInterface;

class Module implements ApigilityProviderInterface
{
    public function getConfig()
    {
        return array(
            'zf-versioning' => array(
                'default_version' => 123,
            ),
        );
    }
}
