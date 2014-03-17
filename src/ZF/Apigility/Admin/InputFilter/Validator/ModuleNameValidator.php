<?php

namespace ZF\Apigility\Admin\InputFilter\Validator;

class ModuleNameValidator extends AbstractValidator
{
    const API_NAME = 'api_name';

    protected $messageTemplates = array(
        self::API_NAME => "'%value%' is not a valid api name"
    );

    public function isValid($value)
    {
        $this->setValue($value);

        if (!$this->isValidWordInPhp($value)) {
            $this->error(self::API_NAME);
            return false;
        }

        return true;
    }
}
