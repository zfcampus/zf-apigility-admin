<?php

namespace Test\Foo;

use ZF\Apigility\ApigilityModuleInterface;

class Module implements ApigilityModuleInterface
{
    public function getConfig()
    {
        return array(
            'zf-versioning' => array(
                'default-version' => 123,
            ),
        );
    }
}
