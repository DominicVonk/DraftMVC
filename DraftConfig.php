<?php
namespace DraftMVC;
class DraftConfig {
    private static $config;
    public static function get($path) {
        $path = explode('.', $path);
        $config = self::$config;
        foreach($path as $name) {
            $config = $config[$name];
        }
        return $config;
    }
    public static function load($file) {
        self::$config[pathinfo($file, PATHINFO_FILENAME)] = json_decode(file_get_contents(DRAFT_CONFIGS.'/'.$file), true);
    }
}