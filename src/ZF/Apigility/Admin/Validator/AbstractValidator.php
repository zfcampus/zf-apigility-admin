<?php

namespace ZF\Apigility\Admin\Validator;

use Zend\Validator\AbstractValidator as BaseAbstractValidator;

abstract class AbstractValidator extends BaseAbstractValidator
{

    public function isValidWordInPhp($word)
    {
        $reservedWords = array(
            '__halt_compiler', 'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch',
            'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else',
            'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile',
            'eval', 'exit', 'extends', 'final', 'for', 'foreach', 'function', 'global', 'goto', 'if',
            'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'isset',
            'list', 'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 'require',
            'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use',
            'var', 'while', 'xor'
        );

        // can't start with _
        if (!preg_match('/^[a-zA-Z\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $word) ||
            in_array(strtolower($word), $reservedWords)) {
            return false;
        }
        return true;
    }
}