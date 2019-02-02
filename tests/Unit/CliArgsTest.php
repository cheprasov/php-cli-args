<?php
/**
 * This file is part of CliArgs.
 * git: https://github.com/cheprasov/php-cli-args
 *
 * (C) Alexander Cheprasov <acheprasov84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Test\Unit;

use CliArgs\CliArgs;

class CliArgsTest extends \PHPUnit_Framework_TestCase
{
    public function providerTestConfig()
    {
        return [
            [[], null],
            [
                ['foo', 'bar'],
                [
                    'foo' => ['key' => 'foo', 'alias' => null, 'default' => null, 'help' => null, 'filter' => null],
                    'bar' => ['key' => 'bar', 'alias' => null, 'default' => null, 'help' => null, 'filter' => null],
                ]
            ],
            [
                ['foo' => 'f', 'bar' => 'b', 'help'],
                [
                    'foo' => ['key' => 'foo', 'alias' => 'f', 'default' => null, 'help' => null, 'filter' => null],
                    'bar' => ['key' => 'bar', 'alias' => 'b', 'default' => null, 'help' => null, 'filter' => null],
                    'help' => ['key' => 'help', 'alias' => null, 'default' => null, 'help' => null, 'filter' => null],
                ]
            ],
            [
                [
                    'help' => [],
                    'foo' => [
                        'alias' => 'f',
                        'default' => 'bar',
                        'help' => 'Some help text',
                        'filter' => ['a', 'b', 'c']
                    ]
                ],
                [
                    'help' => ['key' => 'help', 'alias' => null, 'default' => null, 'help' => null, 'filter' => null],
                    'foo' => ['key' => 'foo', 'alias' => 'f', 'default' => 'bar', 'help' => 'Some help text', 'filter' => ['a', 'b', 'c']],
                ]
            ],
        ];
    }

    /**
     * @see \CliArgs\CliArgs::setConfig
     * @dataProvider providerTestConfig
     * @param $config
     * @param $expect
     */
    public function testConfig($config, $expect)
    {
        $CliArgs = new CliArgs();
        $Reflection = new \ReflectionClass($CliArgs);
        $Method = $Reflection->getMethod('setConfig');
        $Method->setAccessible(true);
        $Method->invoke($CliArgs, $config);
        $Property = $Reflection->getProperty('config');
        $Property->setAccessible(true);
        $result = $Property->getValue($CliArgs);
        $this->assertEquals($expect, $result);
    }

    public function providerTestParseArray()
    {
        return [
            [
                [__FILE__, '-f', 'b', '--foo', 'bar'],
                null,
                [__FILE__, 'f' => 'b', 'foo' => 'bar']
            ],
            [
                ['a', 'b', 'c', '-foo', '--bar'],
                false,
                ['a', 'b', 'c', 'f' => false, 'o' => false, 'bar' => false]
            ],
            [
                ['-f', '-b', 'foo', '--foo=123'],
                0,
                ['f' => 0, 'b' => 'foo', 'foo' => '123']
            ],
            [
                ['-f=1', '-b'],
                0,
                ['f' => 0, '=' => 0, 1 => 0, 'b' => 0]
            ],
            [
                ['a', 'b', 'c'],
                null,
                ['a', 'b', 'c']
            ],
            [
                ['-abc'],
                10,
                ['a' => 10, 'b' => 10, 'c' => 10]
            ],
        ];
    }

    /**
     * @see \CliArgs\CliArgs::parseArray
     * @dataProvider providerTestParseArray
     * @param array $argv
     * @param mixed $default
     * @param mixed $expect
     */
    public function testParseArray($argv, $default, $expect)
    {
        $CliArgs = new CliArgs();
        $Reflection = new \ReflectionClass($CliArgs);
        $Method = $Reflection->getMethod('parseArray');
        $Method->setAccessible(true);
        $result = $Method->invoke($CliArgs, $argv, $default);
        $this->assertEquals($expect, $result);
    }

    public function providerTestFilterValue()
    {
        return [
            __LINE__ => ['int', '042', 0, 42],
            __LINE__ => ['int', '42a', 0, 42],
            __LINE__ => ['int', '-42', 0, -42],
            __LINE__ => ['int', null, 0, 0],

            __LINE__ => ['float', '04.2', 0.0, 4.2],
            __LINE__ => ['float', '4.2e2', 0.0, 420.0],
            __LINE__ => ['float', '-4.2e2', 0.0, -420.0],
            __LINE__ => ['float', null, 0.0, 0.0],

            __LINE__ => ['bool', 'true', false, true],
            __LINE__ => ['bool', 'True', false, true],
            __LINE__ => ['bool', 'TRUE', false, true],
            __LINE__ => ['bool', 'Yes', false, true],
            __LINE__ => ['bool', 'YES', false, true],
            __LINE__ => ['bool', '1', false, true],
            __LINE__ => ['bool', null, null, null],
            __LINE__ => ['bool', null, false, false],
            __LINE__ => ['bool', 'No', false, false],
            __LINE__ => ['bool', 'False', null, false],
            __LINE__ => ['bool', 'false', null, false],
            __LINE__ => ['bool', '0', null, false],

            __LINE__ => ['json', '"123"', null, '123'],
            __LINE__ => ['json', '[1, 2, "3"]', null, [1, 2, '3']],
            __LINE__ => ['json', '{"foo":"bar", "baz":42}', null, ['foo' => 'bar', 'baz' => 42]],
            __LINE__ => ['json', 'bad', null, null],
            __LINE__ => ['json', 'bad', false, false],

            __LINE__ => [['1', '2', '3'], 1, false, false],
            __LINE__ => [['1', '2', '3'], '1', false, '1'],
            __LINE__ => [['1', '2', '3'], '3', false, '3'],
            __LINE__ => [['1', '2', '3'], '5', false, false],
            __LINE__ => [['foo', 'bar', 'baz'], 'bar', false, 'bar'],
            __LINE__ => [['foo', 'bar', 'baz'], 'bazz', 'foo', 'foo'],

            __LINE__ => [function($a){ return $a * 2;}, '21', null, 42],
            __LINE__ => [function($a, $default){ return $a + $default;}, '32', 10, 42],
            __LINE__ => [[static::class,'strtotitle'], 'alexander', null, 'Alexander'],
            __LINE__ => [[self::class,'strtotitle'], 'alexander cheprasov', null, 'Alexander Cheprasov'],
        ];
    }

    /**
     * @param string $str
     * @return string
     */
    public static function strtotitle($str)
    {
        return mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * @see \CliArgs\CliArgs::filterValue
     * @dataProvider providerTestFilterValue
     * @param string|array|callable $filter
     * @param string $value
     * @param mixed $default
     * @param mixed $expect
     */
    public function testFilterValue($filter, $value, $default, $expect)
    {
        $CliArgs = new CliArgs();
        $Reflection = new \ReflectionClass($CliArgs);
        $Method = $Reflection->getMethod('filterValue');
        $Method->setAccessible(true);
        $result = $Method->invoke($CliArgs, $filter, $value, $default);
        $this->assertEquals($expect, $result);
    }
}
