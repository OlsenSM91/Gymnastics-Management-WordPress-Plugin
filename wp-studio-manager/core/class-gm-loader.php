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
        $relative = strtolower(str_replace('WSM\\', '', $class));
        $relative = str_replace('\\', '/', $relative);
        $file = WSM_PLUGIN_DIR . 'core/' . $relative . '.php';
        if (file_exists($file)) {
            include $file;
        }
    }
}
