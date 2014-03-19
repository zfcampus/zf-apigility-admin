<?php

namespace ZF\Apigility\Admin\InputFilter\Validator;

use Zend\Validator\Exception;

class HttpMethodArrayValidator extends AbstractValidator
{
    const HTTP_METHOD_ARRAY = 'httpMethodArray';

    protected $validHttpMethods = array(
        'OPTIONS',
        'GET',
        'POST',
        'PATCH',
        'PUT',
        'DELETE'
    );


    protected $messageTemplates = array(
        self::HTTP_METHOD_ARRAY => "'%value%' is not http method"
    );

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value)
    {
        foreach ($value as $httpMethod) {
            if (!in_array($httpMethod, $this->validHttpMethods)) {
                $this->error(self::HTTP_METHOD_ARRAY, $httpMethod);
                return false;
            }
        }
        return true;
    }
}
