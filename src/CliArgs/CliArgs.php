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
    const FILTER_INT   = 'int';
    const FILTER_FLOAT = 'float';
    const FILTER_BOOL  = 'bool';
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
    public function setConfig(array $config = null)
    {
        $this->config = $config;
        $this->cache = [];
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
        if (array_key_exists($arg, $this->cache)) {
            return $this->cache[$arg];
        }
        $arguments = $this->getArguments();

        if (isset($cfg['long']) && isset($arguments[$cfg['long']])) {
            $value = $arguments[$cfg['long']];
        } elseif (isset($cfg['short']) && isset($arguments[$cfg['short']])) {
            $value = $arguments[$cfg['short']];
        } elseif (isset($cfg['default'])) {
            return $cfg['default'];
        } else {
            return null;
        }

        if (isset($cfg['filter'])) {
            $value = $this->filterValue($cfg['filter'], $value, isset($cfg['default']) ? $cfg['default'] : null);
        }

        if (isset($cfg['long'])) {
            $this->cache[$cfg['long']] = $value;
        }
        if (isset($cfg['short'])) {
            $this->cache[$cfg['short']] = $value;
        }

        return $value;
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

                case self::FILTER_HELP:
                    return $this->getHelp($value);

                default:
                    if (preg_match($filter, $value)
                        && preg_last_error() == PREG_NO_ERROR) {
                        return $value;
                    }
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
    protected function getHelp($value)
    {
        $lines = [];
        foreach ($this->config as $key => $cfg) {
            $lines[] = [
                '--' . $key . (isset($cfg['short']) ? ' or -' . $cfg['short'] : ''),
                isset($cfg['help']) ? $cfg['help'] : '',
            ];
        }
        return $value;
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
