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
namespace Test\Functional;

class ScriptTest extends \PHPUnit_Framework_TestCase
{
    public function providerTestScript()
    {
        return [
            __LINE__ => [
                'args' => '',
                'expect' => '[]',
            ],
            __LINE__ => [
                'args' => '--start https://github.com/ -i "cache/notimage.txt" -w 0 -u spider -l 100',
                'expect' => '{"i":"cache\/notimage.txt","start":"https:\/\/github.com\/","w":0}',
            ],
            __LINE__ => [
                'args' => '--start=https://github.com/ -i "cache/notimage.txt" -w 0 -l 100',
                'expect' => '{"i":"cache\/notimage.txt","start":"https:\/\/github.com\/","w":0}',
            ],
            __LINE__ => [
                'args' => '--start data-start --limit 12 --ignore data-ignore --user-agent "data user agent" --verbose 1 --wait 42 --cache-method 10 --id id42 --restart 2',
                'expect' => '{"cache-method":10,"id":"id42","ignore":"data-ignore","limit":12,"restart":"2","start":"data-start","user-agent":"data user agent","verbose":1,"wait":42}',
            ],
            __LINE__ => [
                'args' => '--start=data-start --limit=12 --ignore=data-ignore --user-agent="data user agent" --verbose=1 --wait=42 --cache-method=10 --id=id42 --restart=2',
                'expect' => '{"cache-method":10,"id":"id42","ignore":"data-ignore","limit":12,"restart":"2","start":"data-start","user-agent":"data user agent","verbose":1,"wait":42}',
            ],
            __LINE__ => [
                'args' => '-s data-start -l 12 -i data-ignore -u "data user agent" -v 1 -w 42 -c 10 -x id42 -r 2',
                'expect' => '{"c":10,"i":"data-ignore","l":12,"r":"2","s":"data-start","u":"data user agent","v":1,"w":42,"x":"id42"}',
            ],
            __LINE__ => [
                'args' => '--start https://github.com/ -i "cache/notimage.txt" -w 0 -u spider -l 100',
                'expect' => '{"i":"cache\/notimage.txt","start":"https:\/\/github.com\/","w":0}',
            ],
            __LINE__ => [
                'args' => '--flag',
                'expect' => '{"flag":true}',
            ],
        ];
    }

    /**
     * @see \CliArgs\CliArgs::isFlagOrAliasExists
     * @dataProvider providerTestScript
     * @param array $argv
     * @param string $arg
     * @param bool $expect
     */
    public function testScript($args, $expect, $expectedArgs = null)
    {
        $script = __DIR__ . '/testScript.php';
        $result = `php $script $args`;
        $this->assertEquals($expect, $result);
    }
}
