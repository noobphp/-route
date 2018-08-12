<?php
namespace Noob\Route;

use Noob\Http\Request;
use Noob\Factory\Factory;

/**
 * Created by PhpStorm.
 * User: pxb
 * Date: 2018/8/7
 * Time: 上午10:50
 */

class Route
{
    protected static $map = [];
    protected static $object;
    protected static $target = ['class' => '','method' => '','middleware' => []];
    protected static $temp_middleware = [];
    protected static $prefix_namespace;

    private function __construct()
    {

    }

    public static function middlewareStart($middleware)
    {
        self::$temp_middleware = is_array($middleware) ? $middleware : [$middleware];
    }

    public static function middlewareEnd()
    {
        self::$temp_middleware = [];
    }

    public static function groupStart($prefix_namespace = '', $middleware = [])
    {
        self::$prefix_namespace = trim($prefix_namespace, '\\');
        self::middlewareStart($middleware);
    }

    public static function groupEnd()
    {
        self::$prefix_namespace = '';
        self::middlewareEnd();
    }

    public static function getRoute()
    {
        list($similar, $params) = self::findSimilar();
        if (empty($similar)) {
            return false;
        }
        if (false === strpos(self::getDelimiter(), $similar['namespace'])) {
            $similar['namespace'] .= self::getDelimiter();
        }
        list(self::$target['class'], self::$target['method']) = explode(self::getDelimiter(), $similar['namespace']);
        if (isset($similar['middleware'])) {
            self::$target['middleware'] = array_merge(self::$temp_middleware, $similar['middleware']);
        }
        self::$target['params'] = $params;
        return self::$target;
    }

    protected static function findSimilar()
    {
        $similar = [];
        $params = [];
        $request_uri = trim($_SERVER['REQUEST_URI'], '/');
        $request_uri_arr = explode('/', $request_uri);
        foreach (self::$map as $key => $item) {
            $status = true;
            foreach (explode('/', $item['route']) as $route_key => $route_slice) {
                $route_slice_length = strlen($route_slice);
                if ($route_slice && $route_slice[0] === '{' && $route_slice[$route_slice_length - 1] === '}') {
                    $rule = mb_substr($route_slice, 1, $route_slice_length - 2);
                    if ($rule === 'all') {
                        $similar = $item;
                        $params = array_slice($request_uri_arr, $route_key);
                        break 2;
                    } elseif ($rule === 'one' && ! isset($request_uri_arr[$route_key])) {
                        $status = false;
                        break;
                    }
                    if (isset($request_uri_arr[$route_key])) {
                        $params[] = $request_uri_arr[$route_key];
                    }
                } elseif (! isset($request_uri_arr[$route_key]) || $request_uri_arr[$route_key] !== $route_slice) {
                    $status = false;
                    break;
                }
            }
            if ($status && ! isset($request_uri_arr[$route_key + 1])) {
                $similar = $item;
                break;
            }
        }
        return [$similar, $params];
    }

    protected static function getDelimiter()
    {
        return '@';
    }

    public static function getInstance()
    {
        if (! self::$object) {
            self::$object = new Route();
        }
        return self::$object;
    }

    public static function __callStatic($name, $arguments)
    {
        // TODO: Implement __callStatic() method.
        if (isset($arguments[1])) {
            $request = Factory::getInstance(Request::class);
            switch ($name) {
                case 'get':
                    if (! $request->isGet()) {
                        return self::getInstance();
                    }
                    break;
                case 'post':
                    if (! $request->isPost()) {
                        return self::getInstance();
                    }
                    break;
                default:
                    return false;
                    break;
            }
            $item = ['route' => trim($arguments[0], '/'),'namespace' => self::$prefix_namespace.'\\'.$arguments[1]];
            if (! empty($arguments[2])) {
                $item['middleware'] = is_array($arguments[2]) ? $arguments[2] : [$arguments[2]];
                $item['middleware'] = array_merge(self::$temp_middleware, $item['middleware']);
            } else {
                $item['middleware'] = self::$temp_middleware;
            }
            self::$map[] = $item;
        }
        return self::getInstance();
    }

    public function __clone()
    {
        // TODO: Implement __clone() method.
        return self::getInstance();
    }
}
