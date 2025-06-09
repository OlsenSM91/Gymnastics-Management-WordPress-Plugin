<?php
namespace WSM\Core;

class WSM_Activator {
    public static function activate() {
        // Set up database tables and flush rewrite rules.
        WSM_Database::install();
        flush_rewrite_rules();
    }
}
