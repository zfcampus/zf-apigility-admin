<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\Authentication;

use Zend\InputFilter\InputFilter;

class DigestInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name' => 'accept_schemes',
        ));
        $this->add(array(
            'name' => 'realm',
        ));
        $this->add(array(
            'name' => 'htdigest',
        ));
        $this->add(array(
            'name' => 'nonce_timeout',
        ));
        $this->add(array(
            'name' => 'digest_domains',
        ));
    }
}
