<?php
namespace WSM\Core;

class WSM_Plugin {
    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function run() {
        // Additional bootstrapping can occur here
    }
}
