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

$res = CliArgs::parse($argv, true);
