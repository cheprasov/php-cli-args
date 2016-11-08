<?php
/**
 * This file is part of RedisClient.
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
            'short' => 'h',
            'info' => 'some text',
            'filter' => 'bool',
        ],
    'count' => [
            'short' => 'c',
            'info' => 'count of bla',
            'default' => 0,
            'filter' => 'int'
        ],
    'user-id' => [
            'long'  => 'user-id',
            'info' => 'Id of user',
            'default' => 0,
            'filter' => 'int',
        ],
    'type' => [
            'short' => 't',
            'info' => 'type please',
            'default' => 'a',
            'filter' => ['a', 'b', 'c'],
        ],
    'alias' => [
        'short'  => 'a',
        'info' => 'type please',
        'default' => 'a',
        'filter' => '/^a\d{2}$/',
    ],
];
$CliArgs = new CliArgs($config);
var_dump('c', $CliArgs->getArg('c'));
var_dump('count', $CliArgs->getArg('count'));
var_dump('type', $CliArgs->getArg('type'));
var_dump('alias', $CliArgs->getArg('alias'));
