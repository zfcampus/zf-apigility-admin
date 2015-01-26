<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\RestService;

use Zend\InputFilter\InputFilter;

class PostInputFilter extends InputFilter
{
    /**
     * @var array
     */
    protected $localMessages;

    /**
     * @var bool
     */
    protected $isUpdate = false;

    /**
     * Initialize input filter
     */
    public function init()
    {
        $this->add(array(
            'name' => 'service_name',
            'required' => false,
            'validators' => array(
                array('name' => 'ZF\Apigility\Admin\InputFilter\Validator\ServiceNameValidator'),
            ),
        ));
        $this->add(array(
            'name' => 'adapter_name',
            'required' => false,
        ));
        $this->add(array(
            'name' => 'table_name',
            'required' => false,
        ));
    }

    /**
     * Override isValid to provide conditional input checking
     * @return bool
     */
    public function isValid()
    {
        if (!$this->isValidService()) {
            return false;
        }

        return parent::isValid();
    }

    /**
     * Override getMessages() to ensure our conditional logic messages can be passed upward
     * @return array
     */
    public function getMessages()
    {
        if (is_array($this->localMessages) && !empty($this->localMessages)) {
            return $this->localMessages;
        }
        return parent::getMessages();
    }

    /**
     * Is the service valid?
     *
     * Ensures that one of the following is present:
     *
     * - service_name OR
     * - adapter_name AND table_name
     *
     * @return bool
     */
    protected function isValidService()
    {
        $context = $this->getRawValues();

        if ((!isset($context['service_name']) || $context['service_name'] === null)
            && (!isset($context['adapter_name']) || $context['adapter_name'] === null)
            && (!isset($context['table_name']) || $context['table_name'] === null)
        ) {
            $this->localMessages = array(
                'service_name' => 'You must provide either a Code-Connected service name'
                    . ' OR a DB-Connected database adapter and table name',
            );
            return false;
        }

        if ($this->isUpdate) {
            $this->get('service_name')->setRequired(true);
            return true;
        }

        if (isset($context['service_name']) && $context['service_name'] !== null) {
            if ((isset($context['adapter_name']) && $context['adapter_name'] !== null)
                || (isset($context['table_name']) && $context['table_name'] !== null)
            ) {
                $this->localMessages = array(
                    'service_name' => 'You must provide either a Code-Connected service name'
                        . ' OR a DB-Connected database adapter and table name',
                );
                return false;
            }
            return true;
        }

        if ((isset($context['adapter_name']) && !empty($context['adapter_name']))
            && (!isset($context['table_name']) || $context['table_name'] === null)
        ) {
            $this->localMessages = array(
                'table_name' => 'DB-Connected services require both a database adapter and table name',
            );
            return false;
        }

        if ((!isset($context['adapter_name']) || $context['adapter_name'] === null)
            && (isset($context['table_name']) && !empty($context['table_name']))
        ) {
            $this->localMessages = array(
                'adapter_name' => 'DB-Connected services require both a database adapter and table name',
            );
            return false;
        }

        return true;
    }
}
