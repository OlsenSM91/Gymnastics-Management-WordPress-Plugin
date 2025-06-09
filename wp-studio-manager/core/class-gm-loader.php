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
        $relative = substr($class, 4); // Remove "WSM\\"
        $relative = str_replace('\\', '/', $relative);
        $parts    = explode('/', $relative);
        $class_name = array_pop($parts);
        $path  = strtolower(implode('/', $parts));
        $file  = WSM_PLUGIN_DIR . ($path ? $path . '/' : '');
        $file .= 'class-' . strtolower(str_replace('_', '-', $class_name)) . '.php';

        if (file_exists($file)) {
            include $file;
        }
    }
}
