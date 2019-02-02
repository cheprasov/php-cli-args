[![MIT license](http://img.shields.io/badge/license-MIT-brightgreen.svg)](http://opensource.org/licenses/MIT)
# CliArgs v3.0.0 for PHP >= 5.5

## About
Class **CliArgs** helps to get options from the command line argument list easy.

## Features
- CliArgs uses **$GLOBAL['argv']** as base, and it does not use function **getopt()**.
- It does not implement logic for 'required', it should be in your side.
- It helps to get options easy from command line argument list.
- It generates help info about options based on config.
- Flexible configuration and data filtering.

## Note
This class is not workable when [register_argc_argv](http://php.net/manual/en/ini.core.php#ini.register-argc-argv) is disabled.

## Usage

### Example

run
```
> example.php --name=Alexander --age=32 --sex=m
or
> example.php -n Alexander -a 32 -s m
```

example.php
```php
$config = [
    'name' => 'n',
    'age' => 'a',
    'sex' => 's'
];
$CliArgs = new CliArgs($config);

echo $CliArgs->getArg('name'); // Alexander
echo $CliArgs->getArg('n'); // Alexander

echo $CliArgs->getArg('age'); // 42
echo $CliArgs->getArg('a'); // 42

echo $CliArgs->getArg('sex'); // m
echo $CliArgs->getArg('s'); // m
```

### Config

Note: all params from cli that you want to use should be specified in config, otherwise they will be ignored.
```php
$config = [
    // You should specify key as name of option from the command line argument list.
    // Example, name <param-name> for --param-name option
    'param-name' => [

        'alias' => 'p',
            // [optional], [string]
            // Alias helps to have short or long name for this key.
            // Example, name <p> for -p option

        'default' => false,
            // [optional], [mixed], [default = null]
            // Default value will returned if param is not setted
            // or params has not value.

        'help' => 'Some description about param',
            // [optional], [string]
            // Text that will returned, if you request help

        'filter' => 'int',
            // [optional], [string | array | callable]
            // Filter for the return value.
            // You can use next filters: flag, bool, int, float, help, json, <array>, <function>

            // 'int' - cast to integer before return.
            // 'float' - cast to float before return.
            // 'bool' - cast to bool before return. Yes, true, 1 = TRUE, other = FALSE
            // 'json' - decode JSON data before return.
            // 'flag' - will return TRUE, if key is exists in command line argument list, otherwise - FALSE
            // <array> - use array for enums. Example use ['a', 'b', 'c'] to get only one of these.
            // <callable> - use function($value, $default) { ... } to process value by yourself
    ]
];

$CliArgs = new CliArgs($config);
```

Examples of config:

Example 1
```php

// Simple configs

// The config1 and config2 are equal
$config1 = ['foo', 'bar', 'a'];
$config2 = [
    'foo' => [],
    'bar' => [],
    'a' => [],
];

// The config3 and config4 are equal
$config3 = ['foo' => 'f', 'bar' => 'b', 'a'];
$config4 = [
    'foo' => [
        'alias' => 'f',
    ],
    'bar' => [
        'alias' => 'b',
    ],
    'a' => [],
];
```

Example 2
```php
$config = [
    'help' => [
        'alias' => 'h',
        'help' => 'Show help about all options',
    ],
    'data' => [
        'alias' => 'd',
        'filter' => 'json',
        'help' => 'Some description about this param',
    ],
    'user-id' => [
        'alias' => 'u',
        'filter' => 'int',
        'help' => 'Some description about this param',
    ]
];
$CliArgs = new CliArgs($config);
```
```
Show help
> some-script.php --help
<?php if ($CliArgs->isFlagExist('help', 'h')) echo $CliArgs->getHelp('help'); ?>

Show help only for param data
> some-script.php --help data
<?php if ($CliArgs->isFlagExist('help', 'h')) echo $CliArgs->getHelp('help'); ?>

Show help for all params data
> some-script.php --help data
<?php if ($CliArgs->isFlagExist('help', 'h')) echo $CliArgs->getHelp(); ?>

All the same:
> some-script.php --data='{"foo":"bar"}' --user-id=42
or
> some-script.php --data '{"foo":"bar"}' --user-id 42
or
> some-script.php -d '{"foo":"bar"}' --user-id 42
or
> some-script.php -d '{"foo":"bar"}' -u 42

<?php
    print_r($CliArgs->getArg('data'));
    print_r($CliArgs->getArg('d'));
    print_r($CliArgs->getArg('user-id'));
    print_r($CliArgs->getArg('u'));
```

Example 3
```php
    $config = [
        'flag' => [
            'alias' => 'f',
            'filter' => 'flag',
        ],
        'id' => [
            'filter' => 'int',
        ],
        'any' => [],
    ];
```
```
> some-script.php --flag

> some-script.php -f

> some-script.php -f --id=42 --any="any value"

> some-script.php --any="any value"

<?php
    print_r($CliArgs->isFlagExist('flag', 'f'));
    print_r($CliArgs->getArg('data'));
    print_r($CliArgs->getArg('d'));
    print_r($CliArgs->getArg('user-id'));
    print_r($CliArgs->getArg('u'));

```

Example 4
```php
    $config = [
        'name' => [
            'alias' => 'n',
            'filter' => function($name, $default) {
                return $name ? mb_convert_case($name, MB_CASE_TITLE, 'UTF-8') : $defult;
            },
            'default' => 'No name',
        ],
        'sex' => [
            'alias' => 's',
            'filter' => ['m', 'f'],
            'default' => null,
        ],
        'city' => [
            'alias' => 'c',
            'filter' => function($city) {
                // ... some checks of city
            },
        ],
        'email' => [
            'alias' => 'e',
            'filter' => function($city) {
                // ... some checks of email
            },
        ]
    ];
```
```
> some-script.php --name alexander

> some-script.php -f

> some-script.php -f --id=42 --any="any value"

> some-script.php --any="any value"

<?php
    print_r($CliArgs->getArg('name'));
    print_r($CliArgs->('d'));
    print_r($CliArgs->getArg('user-id'));
    print_r($CliArgs->getArg('u'));
```

### Create a new instance
```php
// simple config
$config = ['foo' => 'f', 'bar' => 'b'];
$CliArgs = new CliArgs($config);
```

### Methods

```
> example.php --foo Hello --bar World
```

##### new CliArgs(array|null $config = null)
Constructor. If config contents wrong aliases then ConfigErrorException will be thrown.
```php
$config = ['foo' => 'f', 'bar' => 'b'];
$CliArgs = new CliArgs($config);
```

##### getArgs(): array
The method returns all passed arguments which is specified in config.
```php
$argv = $CliArgs->getArgs();
print_r($argv);
// array(
//    'foo' => 'Hello',
//    'bar' => 'World',
// )
```

##### getArg(string $key): mixed | null
Returns value for argument by key. If argument is not set, then it will return default value or `null`
```php
$arg = $CliArgs->getArg('foo');
// or $CliArgs->getArg('f');
echo $arg; // Hello
```

##### isFlagExist(string $key, boolean $checkAlias = true): bool
Return `true` if key exists, otherwise the method returns `false`
If `$checkAlias` is `true`, then the method will check key and alias, and will return `true` if key or alias exists.
If `$checkAlias` is `false`, then the method will check only key and will return `true` only if key exists.
```php
// some_script.php --foo

$CliArgs = new $CliArgs(['foo' => 'f']);

echo $CliArgs->isFlagExist('foo'); // true
echo $CliArgs->isFlagExist('f'); // true

echo $CliArgs->isFlagExist('foo', false); // true
echo $CliArgs->isFlagExist('f', false); // false
```

##### getArguments(): array
Get prepared arguments from ARGV
```php
print_r($CliArgs->getArguments());
// array(
//     0 => 'example.php'
//    'foo' => 'Hello',
//    'bar' => 'World',
// )
```

##### geHelp([string $value = null]): string
Get help
```php
echo $CliArgs->getHelp(); //  Get help for all params
echo $CliArgs->getHelp('help'); //  Get help for secified params: --help data
```

## Installation

### Composer

Download composer:

    wget -nc http://getcomposer.org/composer.phar

and add dependency to your project:

    php composer.phar require cheprasov/php-cli-args

## Running tests

    ./vendor/bin/phpunit

## Something doesn't work

Feel free to fork project, fix bugs and finally request for pull (do not forget write tests please)
