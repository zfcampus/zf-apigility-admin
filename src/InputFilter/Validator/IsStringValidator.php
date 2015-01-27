<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\Validator;

use Zend\Validator\AbstractValidator as ZfAbstractValidator;

class IsStringValidator extends ZfAbstractValidator
{
    const INVALID_TYPE = 'invalidType';

    protected $messageTemplates = array(
        self::INVALID_TYPE => 'Value must be a string; received %value%',
    );

    /**
     * Test if a value is a string
     *
     * @param array $value
     * @return bool
     */
    public function isValid($value)
    {
        if (! is_string($value)) {
            $this->error(self::INVALID_TYPE, (is_object($value) ? get_class($value) : gettype($value)));
            return false;
        }

        return true;
    }
}
