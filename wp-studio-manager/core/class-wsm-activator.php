<?php
namespace WSM\Core;

class WSM_Activator {
    public static function activate() {
        flush_rewrite_rules();
    }
}
