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
        'help' => 'Show help',
        'filter' => 'help',
    ],
    'count' => [
        'short' => 'c',
        'help' => 'count of bla',
        'default' => 0,
        'filter' => 'int'
    ],
    'user-id' => [
        'long'  => 'user-id',
        'help' => 'Id of user',
        'default' => 0,
        'filter' => 'int',
    ],
    'type' => [
        'short' => 't',
        'help' => 'type please',
        'default' => 'a',
        'filter' => ['a', 'b', 'c'],
    ],
    'alias' => [
        'short'  => 'a',
        'help' => 'type please',
        'default' => 'a',
        'filter' => '/^a\d{2}$/',
    ],
    'json' => [
        'short'  => 'j',
        'help' => 'type please',
        'default' => 'a',
        'filter' => 'json',
    ],
    'func' => [
        'short'  => 'f',
        'help' => 'type please',
        'default' => null,
        'filter' => function($val, $default) { return $val * 2; },
    ],
    'key' => [
        'short'  => 'k',
        'default' => null,
    ],
];
$CliArgs = new CliArgs($config);
$key = $CliArgs->getArg('key');
var_dump($key, $CliArgs->getArg($key));

