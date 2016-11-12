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
namespace CliArgs;

class CliArgs
{
    const VERSION = '1.0.0';

    const FILTER_BOOL  = 'bool';
    const FILTER_FLAG  = 'flag';
    const FILTER_FLOAT = 'float';
    const FILTER_INT   = 'int';
    const FILTER_JSON  = 'json';
    const FILTER_HELP  = 'help';

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
     */
    public function __construct(array $config = null)
    {
        $this->setConfig($config);
    }

    /**
     * @param array|null $config
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
                'default' => array_key_exists('default', $cfg) ? $cfg['default'] : null,
                'help' => isset($cfg['help']) ? $cfg['help'] : null,
                'filter' => isset($cfg['filter']) ? $cfg['filter'] : null,
            ];
        }
        $this->config = $newConfig;

        $this->aliases = [];
        foreach ($this->config as $key => $cfg) {
            $this->aliases[$key] = &$this->config[$key];
            if ($cfg['alias']) {
                $this->aliases[$cfg['alias']] = &$this->config[$key];
            }
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
     * @param string|null $alias
     * @return bool
     */
    public function isFlagExists($arg, $alias = null)
    {
        return array_key_exists($arg, $this->getArguments()) || $alias && array_key_exists($alias, $this->getArguments());
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
        if (array_key_exists($arg, $this->cache)) {
            return $this->cache[$cfg['key']];
        }
        $arguments = $this->getArguments();

        if ($this->isFlagExists($cfg['key'])) {
            $value = $arguments[$cfg['key']];
        } elseif ($this->isFlagExists($cfg['alias'])) {
            $value = $arguments[$cfg['alias']];
        } elseif ($cfg['default']) {
            return $cfg['default'];
        } else {
            return null;
        }

        if ($cfg['filter'] && $cfg['default'] !== $value
            || $cfg['filter'] === self::FILTER_FLAG
            || $cfg['filter'] === self::FILTER_HELP
        ) {
            $value = $this->filterValue($cfg['filter'], $value, $cfg['default'] ?: null);
        }

        $this->cache[$cfg['key']] = $value;

        return $value;
    }

    /**
     * @return mixed[]
     */
    public function getArgs()
    {
        $args = [];
        $arguments = $this->getArguments();
        foreach ($arguments as $key => $arg) {
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
                case self::FILTER_FLAG:
                    return true;

                case self::FILTER_BOOL:
                   return filter_var($value, FILTER_VALIDATE_BOOLEAN);

                case self::FILTER_INT:
                   return (int)$value;

                case self::FILTER_FLOAT:
                   return (float)$value;

                case self::FILTER_JSON:
                   return json_decode($value, true);

                case self::FILTER_HELP:
                    return $this->getHelp($value);
            }
            return $default;
        }
        if (is_array($filter)) {
            return in_array($value, $filter, true) ? $value : $default;
        }
        if (is_callable($filter)) {
            return $filter($value, $default);
        }
        return $default;
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
     * @param mixed $value
     * @return string mixed
     */
    protected function getHelp($value = null)
    {
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
