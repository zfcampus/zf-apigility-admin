<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter;

use Zend\InputFilter\Factory as InputFilterFactory;
use Zend\InputFilter\InputFilter;

class InputFilterInputFilter extends InputFilter
{
    /**
     * @var array
     */
    protected $messages = array();

    /**
     * @var InputFilterFactory
     */
    protected $validationFactory;

    /**
     * @param InputFilterFactory $factory
     */
    public function __construct(InputFilterFactory $factory)
    {
        $this->validationFactory = $factory;
    }

    /**
     * Is the data set valid?
     *
     * @return bool
     */
    public function isValid()
    {
        $this->messages = array();
        try {
            $this->validationFactory->createInputFilter($this->data);
            return true;
        } catch (\Exception $e) {
            $this->messages['inputFilter'] = $e->getMessage();
            return false;
        }
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
