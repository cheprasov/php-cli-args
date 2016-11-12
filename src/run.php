<?php
/**
 * This file is part of CliArgs.
 * git: https://github.com/cheprasov/php-cli-args
 *
 * (C) Alexander Cheprasov <cheprasov.84@ya.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
include(__DIR__ . '/autoloader.php');

use CliArgs\CliArgs;

$config = [
    'help' => [
        'alias' => 'h',
        'help' => 'Show help',
        'filter' => 'help',
    ],
    'use-flag' => [
        'alias' => 'f',
        'help' => 'Example how to use flag',
        'default' => false,
        'filter' => 'flag'
    ],
    'user-id' => [
        'alias'  => 'u',
        'help' => 'Example about integers',
        'default' => 0,
        'filter' => 'int',
    ],
    'enum' => [
        'alias' => 'e',
        'help' => 'Example of enum',
        'default' => 'a',
        'filter' => ['a', 'b', 'c'],
    ],
    'json' => [
        'alias'  => 'j',
        'help' => 'Example of json',
        'filter' => 'json',
    ],
    'func' => [
        'alias'  => 'f',
        'help' => 'Example of function for filter',
        'default' => null,
        'filter' => function($val, $default) { return $val * 2; },
    ],
];
$CliArgs = new CliArgs($config);

//var_dump($CliArgs->isArgExists('help'));
var_dump($CliArgs->getArg());
