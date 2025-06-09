<?php
namespace WSM\Core;

class WSM_Deactivator {
    public static function deactivate() {
        flush_rewrite_rules();
    }
}
