<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter;

use Zend\InputFilter\Input;
use Zend\Validator\Regex;

class CreateContentNegotiationInputFilter extends ContentNegotiationInputFilter
{
    public function __construct()
    {
        parent::__construct();

        $this->get('selectors')->setRequired(false);

        $input = new Input('content_name');
        $input->setRequired(true);
        $chain = $input->getValidatorChain();
        $chain->attach(new Validator\IsStringValidator());
        $this->add($input);
    }
}
