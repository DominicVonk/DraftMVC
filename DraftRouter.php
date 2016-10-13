<?php
namespace DraftMVC;
class DraftRouter {
    public static function route($routes) {
        $done = false;
        foreach($routes as $domain => $route) {
            if (self::routeDomain($domain, $route)) {
                $done = true;
                break;
            }
        }
        if (!$done) {
            header('Location: /404');
        }
    }
    private static function routeDomain ($domain, $routes) {
        if (preg_match('/' . str_replace('/', '\\/', $domain) . '/i', $_SERVER['HTTP_HOST'], $matches)) {
            foreach($routes as $method => $route) {
                if (self::routeMethod($method, $route, array_slice($matches, 1))) {
                    return true;
                }
            }
        }
        return;
    }
    private static function routeMethod ($method, $routes, $matches) {
        if (strtolower($_SERVER['REQUEST_METHOD']) === strtolower($method)) {
            foreach($routes as $path => $route) {
                if (self::routePath($path, $route, $matches)) {
                    return true;
                }
            }
        }
        return;
    }
    private static function routePath ($path, $execute, $matches) {
        if (preg_match('/^' . str_replace('/', '\\/', $path) . '(\\?.*|)$/i', $_SERVER['REQUEST_URI'], $matches2)) {
            
            $names = explode('->', $execute);
            $className = $names[0];
            $function = $names[1];
            unset($matches2[count($matches2)-1]);
            $filename = str_replace('\\', '/', $className);
            if (substr($filename, 0, 1) == '/') {
                $filename = substr($filename, 1);
            }
            
            require(DRAFT_CONTROLLERS.'/'.$filename.'.php');
            $class = new $className();
            $matches = array_merge($matches, array_slice($matches2, 1));
            $path = substr($className, strrpos('/' . $className, '/'));
            $path = strtolower(substr($path, 0, -10));
            
            $funcPath = $function;
            $funcPath = preg_replace('/([a-z])([A-Z])/', '$1/$2', $funcPath);
            $funcPath = strtolower($funcPath);
            if (file_exists(DRAFT_VIEWS . '/' . $path . '/' . $funcPath . '.php' )) {
                $view = new \DraftMVC\DraftView($path . '/' .$funcPath);
                call_user_func_array(array($class, 'setView'), array($view));
            }
            if (file_exists(DRAFT_VIEWS . '/layouts/' . $path . '.php' )) {
                $layout = new \DraftMVC\DraftView('layouts/' .$path);
                call_user_func_array(array($class, 'setLayout'), array($layout));
            }
            $return = null;
            $funcMain = '_';
            $pseudo = explode('/', $funcPath);
            for($i = 0; $i < count($pseudo); $i++) {
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
                if(isset($_GET['callback'])) {
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