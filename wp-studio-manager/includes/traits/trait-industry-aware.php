<?php
namespace WSM\Includes\Traits;

use WSM\Includes\Industry_Configs\Industry_Config;

trait Industry_Aware {
    protected function label($key, $plural = false) {
        return Industry_Config::get_label($key, $plural);
    }
}
