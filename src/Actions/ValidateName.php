<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Actions;

use InvalidArgumentException;
use Throwable;

final class ValidateName
{
    /**
     * PHP keywords and reserved words that cannot be used as a class name.
     *
     * @var array<string>
     */
    private const array RESERVED_NAMES = [
        '__halt_compiler', 'abstract', 'and', 'array', 'as', 'bool', 'break', 'callable', 'case', 'catch',
        'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else',
        'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'enum', 'eval',
        'exit', 'extends', 'false', 'final', 'finally', 'float', 'fn', 'for', 'foreach', 'function',
        'global', 'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'int', 'interface',
        'isset', 'iterable', 'list', 'match', 'mixed', 'namespace', 'never', 'new', 'null', 'object',
        'or', 'parent', 'print', 'private', 'protected', 'public', 'readonly', 'require', 'require_once', 'return',
        'self', 'static', 'string', 'switch', 'throw', 'trait', 'true', 'try', 'unset', 'use',
        'var', 'void', 'while', 'xor', 'yield',
    ];

    /**
     * Handle the action.
     *
     * @throws Throwable
     */
    public static function handle(?string $name): void
    {
        if (in_array($name, [null, '', '0'], true)) {
            throw new InvalidArgumentException('Please input the name of the action class.');
        }

        if (! preg_match('/^[A-Za-z_]\w*$/', $name)) {
            throw new InvalidArgumentException('The name provided for the class name is invalid. It uses only letters, numbers, and underscores, and cannot begin with a number.');
        }

        if (in_array(mb_strtolower($name), self::RESERVED_NAMES, true)) {
            throw new InvalidArgumentException("The name {$name} is reserved by PHP and cannot be used as a class name.");
        }
    }
}
