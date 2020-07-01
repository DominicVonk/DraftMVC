<?php

namespace DraftMVC;

if (!defined('DRAFT_CONFIGS')) {
    define('DRAFT_CONFIGS', __DIR__ . '/config');
}
class DraftConfig
{
    private static $config;
    public static function get($path, $default = null)
    {
        if (empty(self::$config)) {
            foreach (glob(DRAFT_CONFIGS . '/*.php') as $file) {
                self::$config[pathinfo($file, PATHINFO_FILENAME)] = require($file);
            }
        }
        $path = explode('.', $path);
        $config = self::$config;
        foreach ($path as $name) {
            if (array_key_exists($name, $config)) {
                $config = $config[$name];
            } else {
                return $default;
            }
        }
        return $config;
    }
}
