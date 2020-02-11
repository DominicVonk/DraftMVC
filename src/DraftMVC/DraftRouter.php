<?php

namespace DraftMVC;

if (!defined('DRAFT_CONTROLLERS')) {
    define('DRAFT_CONTROLLERS', __DIR__ . '/controllers');
}
class DraftRouter
{
    private static $viewClass = '\DraftMVC\DraftView';
    private static $viewExt = 'php';
    private static $layout = true;
    public static function setViewClass($class)
    {
        self::$viewClass = $class;
    }
    public static function setViewExtension($ext)
    {
        self::$viewExt = $ext;
    }
    public static function disableLayoutSearch()
    {
        self::$layout = false;
    }
    public static function route($routes)
    {
        $done = false;
        foreach ($routes as $domain => $route) {
            if (self::routeDomain($domain, $route)) {
                $done = true;
                break;
            }
        }
        if (!$done) {
            header('Location: /404');
        }
    }
    private static function routeDomain($domain, $routes)
    {
        if (preg_match('/' . str_replace('/', '\\/', $domain) . '/i', $_SERVER['HTTP_HOST'], $matches)) {
            foreach ($routes as $method => $route) {
                if (self::routeMethod($method, $route, array_slice($matches, 1))) {
                    return true;
                }
            }
        }
        return;
    }
    private static function routeMethod($method, $routes, $matches)
    {
        if (strtolower($_SERVER['REQUEST_METHOD']) === strtolower($method)) {
            foreach ($routes as $path => $route) {
                if (self::routePath($path, $route, $matches)) {
                    return true;
                }
            }
        }
        return;
    }
    private static function routePath($path, $execute, $matches)
    {
        if (preg_match('/^' . str_replace('/', '\\/', $path) . '(\\?.*|)$/i', $_SERVER['REQUEST_URI'], $matches2)) {

            $names = explode('->', $execute);
            $className = $names[0];
            $function = $names[1];
            unset($matches2[count($matches2) - 1]);
            $filename = str_replace('\\', '/', $className);
            if (substr($filename, 0, 1) == '/') {
                $filename = substr($filename, 1);
            }
            /* If file doesn't exists depend on autoloading */
            if (file_exists(DRAFT_CONTROLLERS . '/' . $filename . '.php')) {
                require(DRAFT_CONTROLLERS . '/' . $filename . '.php');
            }
            $class = new $className();
            $matches = array_merge($matches, array_slice($matches2, 1));
            $path = substr($filename, strrpos('/' . $filename, '/'));
            $path = strtolower(substr($path, 0, -10));
            $funcPath = $function;
            $funcPath = preg_replace('/([a-z])([A-Z])/', '$1/$2', $funcPath);
            $funcPath = strtolower($funcPath);
            if (file_exists(DRAFT_VIEWS . '/' . $path . '/' . $funcPath . '.' . self::$viewExt)) {
                $view = new self::$viewClass($path . '/' . $funcPath);
                call_user_func_array(array($class, 'setView'), array($view));
            }
            if (self::$layout && file_exists(DRAFT_VIEWS . '/layouts/' . $path . '.' . self::$viewExt)) {
                $layout = new self::$viewClass('layouts/' . $path);
                call_user_func_array(array($class, 'setLayout'), array($layout));
            }
            if (method_exists($class, 'init')) {
                call_user_func_array(array($class, 'init'), $matches);
            }
            $return = null;
            $funcMain = '_';
            $pseudo = explode('/', $funcPath);
            for ($i = 0; $i < count($pseudo); $i++) {
                $funcMain .= $i == 0 ? $pseudo[$i] : ucfirst($pseudo[$i]);
                if (method_exists($class, $funcMain)) {
                    call_user_func_array(array($class, $funcMain), $matches);
                }
            }
            if (method_exists($class, $function)) {
                $return = call_user_func_array(array($class, $function), $matches);
            }
            if ($return === null) {
                if ($class->hasView()) {
                    unset($class);
                } else {
                    die('Return an array or create the right view in views/');
                }
            } else {
                $class->unsetLayout();
                $class->unsetView();
                header('Content-Type: application/json');
                if (isset($_GET['callback'])) {
                    echo $_GET['callback'] . '(';
                }
                if (isset($_GET['beautify'])) {
                    echo json_encode($return, JSON_PRETTY_PRINT);
                } else {
                    echo json_encode($return);
                }
                if (isset($_GET['callback'])) {
                    echo ')';
                }
            }
            return true;
        }
        return;
    }
}
