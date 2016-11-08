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

class CliArgs
{

    /**
     * @var array|null
     */
    protected $config;

    /**
     * @var array
     */
    protected $aliases;

    /**
     * @var array|null
     */
    protected $arguments;

    /**
     * @param array $config
     */
    public function __construct(array $config = null)
    {
        $this->setConfig($config);
    }

    public function setConfig(array $config = null)
    {
        $this->config = $config;
        if (!$config) {
            $this->aliases = null;
            return;
        }
        $this->aliases = [];
        foreach ($config as $key => $cfg) {
            $this->aliases[$key] = &$config[$key];
            if (isset($cfg['short'])) {
                $this->aliases[$cfg['short']] = &$config[$key];
            }
            $config[$key]['long'] = $key;
        }
    }

    /**
     * @return array|null
     */
    public function getArguments()
    {
        if (!$this->arguments && isset($GLOBALS['argv']) && is_array($GLOBALS['argv'])) {
            $this->arguments = self::parseArray($GLOBALS['argv']);
        }
        return $this->arguments;
    }

    /**
     * @param string $arg
     * @return mixed
     */
    public function getArg($arg)
    {
        if (!$cfg = $this->getArgFromConfig($arg)) {
            return null;
        }
        $arguments = $this->getArguments();
        if (isset($cfg['long']) && isset($arguments[$cfg['long']])) {
            return $arguments[$cfg['long']];
        }
        if (isset($cfg['short']) && isset($arguments[$cfg['short']])) {
            return $arguments[$cfg['short']];
        }
        return isset($cfg['default']) ? $cfg['default'] : null;
    }

    /**
     * @param $arg
     * @return null
     */
    protected function getArgFromConfig($arg)
    {
        if (isset($this->aliases[$arg])) {
            return $this->aliases[$arg];
        }
        return null;
    }

    /**
     * @param array $argv
     * @param mixed $default
     * @return array
     */
    protected static function parseArray(array $argv, $default = null)
    {
        $result = [];
        $key = null;
        while ($arg = array_shift($argv)) {
            if (0 === strpos($arg, '--')) { // [--param=value] or [--param] or [--param value]
                $pos = strpos($arg, '=');
                if (false === $pos) {
                    $key = substr($arg, 2);
                    $result[$key] = $default;
                } else {
                    $key = substr($arg, 2, $pos - 2);
                    $result[$key] = substr($arg, $pos + 1);
                    $key = null;
                    continue;
                }
            } elseif (0 === strpos($arg, '-')) { // [-a] or [-a b] or [-abc]
                if (2 === strlen($arg)) {
                    $key = $arg[1];
                    $result[$key] = $default;
                } elseif (strlen($arg) > 2) {
                    $arguments = str_split(substr($arg, 1));
                    foreach ($arguments as $a) {
                        $result[$a] = $default;
                    }
                    $key = null;
                    continue;
                }
            } else {
                if ($key) {
                    $result[$key] = $arg;
                    $key = null;
                } else {
                    $result[] = $arg;
                }
            }
        }

        return $result;
    }
}
