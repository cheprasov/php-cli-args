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
namespace CliArgs;

spl_autoload_register(function($class) {
    if (0 !== strpos($class, __NAMESPACE__.'\\')) {
        return;
    }
    $classPath = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($classPath)) {
        include $classPath;
    }
}, false, true);
