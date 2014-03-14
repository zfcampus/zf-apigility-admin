<?php

namespace ZF\Apigility\Admin\Validator;


class ServiceNameValidator extends AbstractValidator
{
    const SERVICE_NAME = 'service_name';

    protected $messageTemplates = array(
        self::SERVICE_NAME => "'%value%' is not a valid service name"
    );

    public function isValid($value)
    {
        $this->setValue($value);

        if (!$this->isValidWordInPhp($value)) {
            $this->error(self::SERVICE_NAME);
            return false;
        }

        return true;
    }
}