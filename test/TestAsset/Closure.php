<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\TestAsset;

/**
 * Class for spying on invokables.
 *
 * Mock this class when you need to mock a closure or invokable class, and do
 * assertions against the `call()` method. Have containers return
 * `[$instance->reveal(), 'call']`.
 */
class Closure
{
    public function call()
    {
    }
}
