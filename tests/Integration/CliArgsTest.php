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
namespace Test\Build;

use CliArgs\CliArgs;

class CliArgsTest extends \PHPUnit_Framework_TestCase {

    public function providerTestFilterHelp()
    {
        return [
            [
                [__FILE__, '--help'],
                ['help' => ['filter' => 'help']],
                "HELP:\n\n"
                ."    --help\n"
            ],
            [
                [__FILE__, '--help'],
                ['help' => ['filter' => 'help', 'alias' => 'h']],
                "HELP:\n\n"
                ."    --help -h\n"
            ],
            [
                [__FILE__, '--help'],
                [
                    'help' => ['filter' => 'help', 'alias' => 'h'],
                    'json' => ['filter' => 'json', 'alias' => 'j', 'help' => 'Example of json'],
                ],
                "HELP:\n\n"
                ."    --help -h\n\n"
                ."    --json -j\n"
                ."        Example of json\n"
            ],
            [
                [__FILE__, '-h'],
                [
                    'help' => ['filter' => 'help', 'alias' => 'h'],
                    'json' => ['filter' => 'json', 'alias' => 'j', 'help' => 'Example of json'],
                ],
                "HELP:\n\n"
                ."    --help -h\n\n"
                ."    --json -j\n"
                ."        Example of json\n"
            ],
            [
                [__FILE__, '-h', 'json'],
                [
                    'help' => ['filter' => 'help', 'alias' => 'h'],
                    'json' => ['filter' => 'json', 'alias' => 'j', 'help' => 'Example of json'],
                ],
                "HELP:\n\n"
                ."    --json -j\n"
                ."        Example of json\n"
            ],
            [
                [__FILE__, '-h', 'j'],
                [
                    'help' => ['filter' => 'help', 'alias' => 'h'],
                    'json' => ['filter' => 'json', 'alias' => 'j', 'help' => 'Example of json'],
                ],
                "HELP:\n\n"
                ."    --json -j\n"
                ."        Example of json\n"
            ],
            [
                [__FILE__, '--help', 'j'],
                [
                    'help' => ['filter' => 'help', 'alias' => 'h'],
                    'json' => ['filter' => 'json', 'alias' => 'j', 'help' => 'Example of json'],
                ],
                "HELP:\n\n"
                ."    --json -j\n"
                ."        Example of json\n"
            ],
            [
                [__FILE__, '--help', 'json'],
                [
                    'help' => ['filter' => 'help', 'alias' => 'h'],
                    'json' => ['filter' => 'json', 'alias' => 'j', 'help' => 'Example of json'],
                ],
                "HELP:\n\n"
                ."    --json -j\n"
                ."        Example of json\n"
            ],
            [
                [__FILE__, '--help', 'json'],
                [
                    'h' => ['filter' => 'help', 'alias' => 'help'],
                    'j' => ['filter' => 'json', 'alias' => 'json', 'help' => 'Example of json'],
                ],
                "HELP:\n\n"
                ."    -j --json\n"
                ."        Example of json\n"
            ],
        ];
    }

    /**
     * @dataProvider providerTestFilterHelp
     * @param array $argv
     * @param array $config
     * @param string $expect
     */
    public function testFilterHelp($argv, $config, $expect)
    {
        $GLOBALS['argv'] = $argv;
        $CliArgs = new CliArgs($config);
        $this->assertEquals(true, $CliArgs->isFlagExists('help', 'h'));
        $this->assertEquals($expect, $CliArgs->getArg('help'));
    }

    public function providerTestFilters()
    {
        return [
            // JSON
            [
                [__FILE__, '--json'],
                ['json' => ['filter' => 'json']],
                'json',
                null
            ],
            [
                [__FILE__, '-j', '{"a":1,"b":2,"c":3}'],
                ['json' => ['filter' => 'json', 'alias' => 'j']],
                'j',
                ['a' => 1, 'b' => 2, 'c' => 3]
            ],
            [
                [__FILE__, '-j', '{"a":1,"b":2,"c":3}'],
                ['j' => ['filter' => 'json', 'alias' => 'json']],
                'j',
                ['a' => 1, 'b' => 2, 'c' => 3]
            ],
            // FLAG
            [
                [__FILE__, '-f'],
                ['f' => ['filter' => 'flag']],
                'f',
                true
            ],
            [
                [__FILE__, '-a'],
                ['f' => ['filter' => 'flag']],
                'f',
                null
            ],
            [
                [__FILE__, '-f'],
                ['f' => ['filter' => 'flag', 'alias' => 'flag']],
                'flag',
                true
            ],
            [
                [__FILE__, '--flag'],
                ['flag' => ['filter' => 'flag', 'alias' => 'f']],
                'f',
                true
            ],
            [
                [__FILE__, '--flag'],
                ['flag' => ['filter' => 'flag', 'alias' => 'f']],
                'f',
                true
            ],
            // BOOL
            [
                [__FILE__, '-b'],
                ['b' => ['filter' => 'bool']],
                'b',
                null
            ],
            [
                [__FILE__, '-b'],
                ['b' => ['filter' => 'bool', 'default' => false]],
                'b',
                false
            ],
            [
                [__FILE__, '-b', 'false'],
                ['b' => ['filter' => 'bool']],
                'b',
                false
            ],
            [
                [__FILE__, '-b', 'NO'],
                ['b' => ['filter' => 'bool']],
                'b',
                false
            ],
            [
                [__FILE__, '-b', '0'],
                ['b' => ['filter' => 'bool']],
                'b',
                false
            ],
            [
                [__FILE__, '-b', '1'],
                ['b' => ['filter' => 'bool']],
                'b',
                true
            ],
            [
                [__FILE__, '-b', 'True'],
                ['b' => ['filter' => 'bool']],
                'b',
                true
            ],
            [
                [__FILE__, '-b', 'YES'],
                ['b' => ['filter' => 'bool']],
                'b',
                true
            ],
            [
                [__FILE__, '-b', 'YES'],
                ['b' => ['filter' => 'bool', 'alias' => 'bool']],
                'bool',
                true
            ],
            // FLOAT
            [
                [__FILE__, '-f'],
                ['f' => ['filter' => 'float']],
                'f',
                null
            ],
            [
                [__FILE__, '-f'],
                ['f' => ['filter' => 'float', 'default' => 0.0]],
                'f',
                0.0
            ],
            [
                [__FILE__, '-f', '123.45'],
                ['f' => ['filter' => 'float', 'default' => 0.0]],
                'f',
                123.45
            ],
            [
                [__FILE__, '--float', '123.45'],
                ['f' => ['filter' => 'float', 'default' => 0.0, 'alias' => 'float']],
                'f',
                123.45
            ],
            // INT
            [
                [__FILE__, '-i'],
                ['i' => ['filter' => 'int']],
                'i',
                null
            ],
            [
                [__FILE__, '-i'],
                ['i' => ['filter' => 'int', 'default' => 0]],
                'i',
                0
            ],
            [
                [__FILE__, '-i', '123'],
                ['i' => ['filter' => 'int', 'default' => 0]],
                'i',
                123
            ],
            [
                [__FILE__, '--foo', '123.45'],
                ['f' => ['filter' => 'int', 'default' => 0, 'alias' => 'foo']],
                'f',
                123
            ],
            [
                [__FILE__, '--foo', '123abc'],
                ['f' => ['filter' => 'int', 'default' => 0, 'alias' => 'foo']],
                'f',
                123
            ],
            // FUNCTION
            [
                [__FILE__, '-i'],
                ['i' => ['filter' => function($a) { return $a * 2;} ]],
                'i',
                null
            ],
            [
                [__FILE__, '-i', '5'],
                ['i' => ['filter' => function($a) { return $a * 2;} ]],
                'i',
                10
            ],
            [
                [__FILE__, '--func', '15'],
                ['i' => ['filter' => function($a) { return $a * 2;} , 'alias' => 'func']],
                'i',
                30
            ],
            // ENUM
            [
                [__FILE__, '-e'],
                ['e' => ['filter' => [1,2,3]]],
                'e',
                null
            ],
            [
                [__FILE__, '-e'],
                ['e' => ['filter' => [1,2,3], 'default' => 0]],
                'e',
                0
            ],
            [
                [__FILE__, '-e', '1'],
                ['e' => ['filter' => [1,2,3], 'default' => 0]],
                'e',
                0
            ],
            [
                [__FILE__, '-e', '1'],
                ['e' => ['filter' => ['1','2','3']]],
                'e',
                '1'
            ],
            [
                [__FILE__, '-e', '2'],
                ['e' => ['filter' => ['1','2','3']]],
                'e',
                '2'
            ],
            [
                [__FILE__, '-e', '4'],
                ['e' => ['filter' => ['1','2','3']]],
                'e',
                null
            ],
        ];
    }

    /**
     * @dataProvider providerTestFilters
     * @param array $argv
     * @param array $config
     * @param string|string[] $arg
     * @param mixed $expect
     */
    public function testFilters($argv, $config, $arg, $expect)
    {
        $GLOBALS['argv'] = $argv;
        $CliArgs = new CliArgs($config);
        $this->assertEquals($expect, $CliArgs->getArg($arg), 'Expected value ' . print_r($expect, true));
    }
}
