<?php

namespace ZF\Apigility\Admin\InputFilter\RestService;

use Zend\InputFilter\InputFilter;

class PostInputFilter extends InputFilter
{
    protected $localMessage;

    public function __construct()
    {
        $this->add(array(
            'name' => 'service_name',
            'validators' => array(
                array('name' => 'ZF\Apigility\Admin\InputFilter\Validator\ServiceNameValidator'),
            ),
        ));
        $this->add(array(
            'name' => 'adapter_name',
        ));
        $this->add(array(
            'name' => 'table_name',
        ));
    }

    public function isValid()
    {
        $context = $this->getRawValues();
        $validationGroup = $this->getValidationGroup($context);

        if (is_string($validationGroup)) {
            $this->localMessage = $validationGroup;
            return false;
        }

        $this->setValidationGroup($validationGroup);
        return parent::isValid();
    }

    public function getMessages()
    {
        if ($this->localMessage) {
            return array(
                'service_name' => array('isValid' => $this->localMessage),
                'adapter_name' => array('isValid' => $this->localMessage),
                'table_name' => array('isValid' => $this->localMessage)
            );
        }
        return parent::getMessages();
    }

    /**
     * @param $context
     * @return array|string
     */
    protected function getValidationGroup($context)
    {
        if ($context['service_name'] === null && $context['adapter_name'] === null && $context['table_name'] === null) {
            return 'Either service_name or adapter_name and table_name must be present';
        }

        if ($context['service_name'] !== null) {
            if ($context['adapter_name'] !== null || $context['table_name'] !== null) {
                return 'service_name cannot be present with adapter_name or table_name';
            } else {
                return array('service_name');
            }
        } else {
            if ($context['adapter_name'] === null && $context['table_name'] === null) {
                return 'adapter_name and table_name must be present if there is no a';
            } else {
                return array('adapter_name', 'table_name');
            }
        }
    }
}
