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
namespace CliArgs;

use CliArgs\Exception\ConfigErrorException;

class CliArgs
{
    const VERSION = '3.0.0';

    const FILTER_BOOL  = 'bool';
    const FILTER_FLAG  = 'flag';
    const FILTER_FLOAT = 'float';
    const FILTER_INT   = 'int';
    const FILTER_JSON  = 'json';

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
     * @var array
     */
    protected $cache = [];

    /**
     * @param array $config
     * @throws ConfigErrorException
     */
    public function __construct(array $config = null)
    {
        $this->setConfig($config);
    }

    /**
     * @param array|null $config
     * @throws ConfigErrorException
     */
    protected function setConfig(array $config = null)
    {
        $this->cache = [];
        if (!$config) {
            $this->config = null;
            $this->aliases = null;
            return;
        }

        $newConfig = [];
        foreach ($config as $key => $cfg) {
            if (is_int($key) && is_string($cfg)) {
                $key = $cfg;
                $cfg = [];
            } elseif (is_string($key) && is_string($cfg)) {
                $cfg = ['alias' => $cfg];
            }
            $newConfig[$key] = [
                'key' => $key,
                'alias' => isset($cfg['alias']) ? $cfg['alias'] : null,
                'default' => isset($cfg['default']) ? $cfg['default'] : null,
                'help' => isset($cfg['help']) ? $cfg['help'] : null,
                'filter' => isset($cfg['filter']) ? $cfg['filter'] : null,
            ];
        }
        $this->config = $newConfig;

        $this->aliases = [];
        foreach ($this->config as $key => $cfg) {
            if (isset($this->aliases[$key])) {
                throw new ConfigErrorException("Key or alias `{$key}` is already defined");
            }
            $this->aliases[$key] = &$this->config[$key];
            if ($cfg['alias']) {
                if (isset($this->aliases[$cfg['alias']])) {
                    throw new ConfigErrorException("Key or alias `{$cfg['alias']}` is already defined");
                }
                $this->aliases[$cfg['alias']] = &$this->config[$key];
            }
        }
    }

    /**
     * Get prepared ARGV
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
     * Checks if the given key exists in the arguments console list. Returns true if $arg or $alias are exists
     * @param string $key
     * @param boolean $checkAlias
     * @return bool
     */
    public function isFlagExist($key, $checkAlias = true)
    {
        $arguments = $this->getArguments();
        if (array_key_exists($key, $arguments)) {
            return true;
        }

        if ($checkAlias && ($alias = $this->getAlias($key))) {
            return array_key_exists($alias, $arguments);
        }

        return false;
    }

    /**
     * @param string $key
     * @return string|null
     */
    protected function getAlias($key)
    {
        if (!isset($this->aliases[$key])) {
            return null;
        }
        $cfg = $this->aliases[$key];
        if ($cfg['key'] === $key) {
            return $cfg['alias'];
        }
        return $cfg['key'];
    }

    /**
     * Get one param
     * @param string $key
     * @return mixed
     */
    public function getArg($key)
    {
        if (!$cfg = $this->getArgConfig($key)) {
            return null;
        }
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$cfg['key']];
        }
        $arguments = $this->getArguments();

        if ($this->isFlagExist($cfg['key'], false)) {
            $value = $arguments[$cfg['key']];
        } elseif ($cfg['alias'] && $this->isFlagExist($cfg['alias'], false)) {
            $value = $arguments[$cfg['alias']];
        } elseif (isset($cfg['default'])) {
            $value = $cfg['default'];
        } else {
            $value = null;
        }

        if ($cfg['filter']) {
            if ($cfg['filter'] === self::FILTER_FLAG) {
                $value = $this->isFlagExist($key);
            } elseif ($cfg['default'] !== $value)  {
                $value = $this->filterValue($cfg['filter'], $value, $cfg['default']);
            }
        }

        $this->cache[$cfg['key']] = $value;

        return $value;
    }

    /**
     * Get all params.
     * @return mixed[]
     */
    public function getArgs()
    {
        $args = [];
        $arguments = $this->getArguments();
        foreach ($arguments as $key => $value) {
            if (!isset($this->aliases[$key])) {
                continue;
            }
            $args[$key] = $this->getArg($key);
        }

        return $args;
    }

    /**
     * @param mixed $filter
     * @param mixed $value
     * @param mixed|null $default
     * @return mixed|null
     */
    protected function filterValue($filter, $value, $default = null)
    {
        if (is_string($filter)) {
            switch ($filter) {
                case self::FILTER_BOOL:
                   return filter_var($value, FILTER_VALIDATE_BOOLEAN);

                case self::FILTER_INT:
                   return (int)$value;

                case self::FILTER_FLOAT:
                   return (float)$value;

                case self::FILTER_JSON:
                   return json_decode($value, true);
            }
            return $default;
        }
        if (is_callable($filter)) {
            return call_user_func($filter, $value, $default);
        }
        if (is_array($filter)) {
            return in_array($value, $filter, true) ? $value : $default;
        }
        return $default;
    }

    /**
     * @param $key
     * @return null
     */
    protected function getArgConfig($key)
    {
        if (isset($this->aliases[$key])) {
            return $this->aliases[$key];
        }
        return null;
    }

    /**
     * Get help about
     * @param string $value
     * @return string mixed
     */
    public function getHelp($value = null)
    {
        if ($value) {
            $value = $this->getArg($value);
        }

        $breakTitle = PHP_EOL . str_repeat(' ', 4);
        $breakInfo = PHP_EOL . str_repeat(' ', 8);
        $help = [];
        foreach ($this->config as $cfg) {
            if ($value && ($cfg['key'] !== $value && (!$cfg['alias'] || $cfg['alias'] !== $value))) {
                continue;
            }
            $title = [];
            if ($cfg['key']) {
                $title[] = (1 === strlen($cfg['key']) ? '-' : '--') . $cfg['key'];
            }
            if ($cfg['alias']) {
                $title[] = (1 === strlen($cfg['alias']) ? '-' : '--') . $cfg['alias'];
            }
            $line = implode(' ', $title);
            if ($cfg['help']) {
                $line .= $breakInfo . wordwrap($cfg['help'], 75, $breakInfo);
            }
            $help[] = $breakTitle . $line;
        }

        return 'HELP:' . PHP_EOL . (implode(PHP_EOL, $help) ?: 'Key is not found') . PHP_EOL;
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
