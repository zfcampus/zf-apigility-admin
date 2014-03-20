<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter;

use Zend\InputFilter\Factory as InputFilterFactory;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterPluginManager;

class InputFilterInputFilter implements InputFilterInterface
{
    protected $inputFilterPluginManager;
    protected $data;
    protected $messages = array();

    public function __construct(InputFilterPluginManager $inputFilterPluginManager = null)
    {
        $this->inputFilterPluginManager = $inputFilterPluginManager;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Is the data set valid?
     *
     * @return bool
     */
    public function isValid()
    {
        $iff = new InputFilterFactory();
        $iff->setInputFilterManager($this->inputFilterPluginManager);
        $this->messages = array();
        try {
            $iff->createInputFilter($this->data);
        } catch (\Exception $e) {
            $this->messages['inputFilter'] = array('isValid' => $e->getMessage());
            return false;
        }
        return true;
    }

    public function getRawValues()
    {
        return $this->data;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    /**#@+
     * Unnecessary methods required by interface for the purposes of this input filter
     * @return void
     */
    public function count()
    {
    }

    public function add($input, $name = null)
    {
    }

    public function get($name)
    {
    }

    public function has($name)
    {
    }

    public function remove($name)
    {
    }

    public function setValidationGroup($name)
    {
    }

    public function getInvalidInput()
    {
    }

    public function getValidInput()
    {
    }

    public function getValue($name)
    {
    }

    public function getValues()
    {
    }

    public function getRawValue($name)
    {
    }
    /**#@-*/
}
