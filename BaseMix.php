<?php

/**
 * Mix类
 * @author 刘健 <coder.liu@qq.com>
 */
class BaseMix
{

    // App实例
    protected static $_app;

    // Mix 目录
    public static $aliases = ['@mix' => __DIR__];

    //
    public static $classMap = [];

    // 主机
    protected static $_host;

    // 公共容器
    public static $container;

    /**
     * 返回App，并设置组件命名空间
     *
     * @return \mix\swoole\Application|\mix\web\Application|\mix\console\Application
     */
    public static function app($namespace = null)
    {
        // 获取App
        $app = self::getApp();
        if (is_null($app)) {
            return $app;
        }
        // 设置组件命名空间
        $app->setComponentNamespace($namespace);
        // 返回App
        return $app;
    }

    /**
     * 获取App
     *
     * @return \mix\swoole\Application|\mix\web\Application|\mix\console\Application
     */
    protected static function getApp()
    {
        if (is_object(self::$_app)) {
            return self::$_app;
        }
        if (is_array(self::$_app)) {
            return self::$_app[self::$_host];
        }
        return null;
    }

    // 设置App
    public static function setApp($app)
    {
        self::$_app = $app;
    }

    // 设置Apps
    public static function setApps($apps)
    {
        self::$_app = $apps;
    }

    // 设置host
    public static function setHost($host)
    {
        self::$_host = null;
        $vHosts      = array_keys(self::$_app);
        foreach ($vHosts as $vHost) {
            if ($vHost == '*') {
                continue;
            }
            if (preg_match("/{$vHost}/i", $host)) {
                self::$_host = $vHost;
                break;
            }
        }
        if (is_null(self::$_host)) {
            self::$_host = isset(self::$_app['*']) ? '*' : array_shift($vHosts);
        }
    }

    // 结束执行
    public static function finish()
    {
        throw new \mix\exception\ExitException('');
    }

    // 打印变量的相关信息
    public static function varDump($var, $exit = false)
    {
        ob_start();
        var_dump($var);
        if ($exit) {
            $content = ob_get_clean();
            throw new \mix\exception\DebugException($content);
        }
    }

    // 打印关于变量的易于理解的信息
    public static function varPrint($var, $exit = false)
    {
        ob_start();
        print_r($var);
        if ($exit) {
            $content = ob_get_clean();
            throw new \mix\exception\DebugException($content);
        }
    }

    // 使用配置创建新对象
    public static function createObject($config)
    {
        // 构建属性数组
        foreach ($config as $key => $value) {
            // 子类实例化
            if (is_array($value) && isset($value['class'])) {
                $subClass = $value['class'];
                unset($value['class']);
                $config[$key] = new $subClass($value);
            }
        }
        // 实例化
        $class = $config['class'];
        unset($config['class']);
        return new $class($config);
    }

    public static function autoload($className)
    {
        if (isset(static::$classMap[$className])) {
            $classFile = static::$classMap[$className];
            if ($classFile[0] === '@') {
                $classFile = static::getAlias($classFile);
            }
        } elseif (strpos($className, '\\') !== false) {
            $classFile = static::getAlias('@' . str_replace('\\', '/', $className) . '.php', false);
            if ($classFile === false || !is_file($classFile)) {
                return;
            }
        } else {
            return;
        }

        include($classFile);

        if (!class_exists($className, false) && !interface_exists($className, false) && !trait_exists($className, false)) {
            throw new UnknownClassException("Unable to find '$className' in file: $classFile. Namespace missing?");
        }
    }

    /**
     * @param $alias
     * @param bool $throwException
     * @return bool|mixed|string
     * @throws InvalidParamException
     */
    public static function getAlias($alias, $throwException = true)
    {
        if (strncmp($alias, '@', 1)) {
            // not an alias
            return $alias;
        }

        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);

        if (isset(static::$aliases[$root])) {
            if (is_string(static::$aliases[$root])) {
                return $pos === false ? static::$aliases[$root] : static::$aliases[$root] . substr($alias, $pos);
            } else {
                foreach (static::$aliases[$root] as $name => $path) {
                    if (strpos($alias . '/', $name . '/') === 0) {
                        return $path . substr($alias, strlen($name));
                    }
                }
            }
        }

        if ($throwException) {
            throw new InvalidParamException("Invalid path alias: $alias");
        } else {
            return false;
        }
    }

    /**
     * @param $alias
     * @param $path
     * @throws InvalidParamException
     */
    public static function setAlias($alias, $path)
    {
        if (strncmp($alias, '@', 1)) {
            $alias = '@' . $alias;
        }
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);
        if ($path !== null) {
            $path = strncmp($path, '@', 1) ? rtrim($path, '\\/') : static::getAlias($path);
            if (!isset(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [$alias => $path];
                }
            } elseif (is_string(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [
                        $alias => $path,
                        $root => static::$aliases[$root],
                    ];
                }
            } else {
                static::$aliases[$root][$alias] = $path;
                krsort(static::$aliases[$root]);
            }
        } elseif (isset(static::$aliases[$root])) {
            if (is_array(static::$aliases[$root])) {
                unset(static::$aliases[$root][$alias]);
            } elseif ($pos === false) {
                unset(static::$aliases[$root]);
            }
        }
    }

}


