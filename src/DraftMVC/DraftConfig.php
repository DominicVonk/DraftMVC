<?php
namespace DraftMVC;
if (!defined('DRAFT_CONFIGS')) {
    define('DRAFT_CONFIGS', __DIR__ . '/config');
}
class DraftConfig {
    private static $config;
    public static function get($path, $default = null) {
        $path = explode('.', $path);
        $config = self::$config;
        foreach($path as $name) {
            if (array_key_exists($name, $config)) {
                $config = $config[$name];
            } else {
                return $default;
            }
        }
        return $config;
    }
    public static function load($file) {
        self::$config[pathinfo($file, PATHINFO_FILENAME)] = json_decode(file_get_contents(DRAFT_CONFIGS.'/'.$file), true);
    }
}
