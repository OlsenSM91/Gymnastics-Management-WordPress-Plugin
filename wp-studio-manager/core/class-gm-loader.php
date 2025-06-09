<?php
namespace WSM\Core;

class GM_Loader {
    public static function register() {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    public static function autoload($class) {
        if (strpos($class, 'WSM\\') !== 0) {
            return;
        }
        $relative = str_replace('WSM\\', '', $class);
        $parts = explode('\\', $relative);
        $class_file = 'class-' . strtolower(str_replace('_', '-', array_pop($parts))) . '.php';
        $path = implode('/', array_map('strtolower', $parts));
        $file = WSM_PLUGIN_DIR . ($path ? $path . '/' : '') . $class_file;
        if (file_exists($file)) {
            include $file;
        }
    }
}
