<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter;

use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class ContentNegotiationInputFilter extends InputFilter
{
    public function __construct()
    {
        $input = new Input('selectors');
        $chain = $input->getValidatorChain();
        $chain->attach(new Validator\ContentNegotiationSelectorsValidator());
        $this->add($input);
    }
}
