<?php
namespace ZF\Apigility\Admin\Validator;

use Zend\Validator\AbstractValidator;

class ModuleName extends AbstractValidator
{
    const MODULENAME = 'modulename';

    protected $reservedWords = array('__halt_compiler', 'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'isset', 'list', 'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var', 'while', 'xor');

    protected $messageTemplates = array(
        self::MODULENAME => "'%value%' is not a valid module name"
    );

    public function isValid($value)
    {
        $this->setValue($value);

        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $value) ||
            in_array(strtolower($value), $this->reservedWords)) {
                $this->error(self::MODULENAME);
                return false;
        }

        return true;
    }
}
