<?php
namespace WSM\Core;

use WSM\Includes\Industry_Configs\Industry_Config;

class WSM_Industry_Config {
    public static function get_label($key, $plural = false) {
        return Industry_Config::get_label($key, $plural);
    }

    public static function get_config($industry = null) {
        return Industry_Config::get_config($industry);
    }
}
