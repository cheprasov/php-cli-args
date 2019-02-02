<?php

include(__DIR__ . '/../../src/autoloader.php');

use CliArgs\CliArgs;

$config = [
    'start' => [
        'alias' => 's',
        'help' => 'Define start',
    ],
    'limit' => [
        'alias' => 'l',
        'default' => 5,
        'help' => 'Define limit',
        'filter' => 'int',
    ],
    'ignore' => [
        'alias' => 'i',
        'default' => null,
        'help' => 'Define ignore',
    ],
    'user-agent' => [
        'alias' => 'u',
        'default' => 'SEO Pocket Crawler',
        'help' => 'Define user-agent',
    ],
    'verbose' => [
        'alias' => 'v',
        'default' => 1,
        'help' => 'Display info',
        'filter' => 'int',
    ],
    'wait' => [
        'alias' => 'w',
        'default' => 100000,
        'help' => 'Define wait',
        'filter' => 'int',
    ],
    'cache-method' => [
        'alias' => 'c',
        'default' => 0,
        'help' => 'Define cache-method',
        'filter' => 'int',
    ],
    'id' => [
        'alias' => 'x',
        'default' => null,
        'help' => 'Permit id',
    ],
    'restart' => [
        'alias' => 'r',
        'default' => 0,
        'help' => 'Define restart',
        'filter' => ['1', '2'],
    ],
    'pause' => [
        'alias' => 't',
        'default' => false,
        'help' => 'Define pause',
    ],
    'flag' => [
        'alias' => 'f',
        'default' => false,
        'filter' => CliArgs::FILTER_FLAG,
    ],
];

$CliArgs = new CliArgs($config);
$args = $CliArgs->getArgs();
ksort($args);

echo json_encode($args);
