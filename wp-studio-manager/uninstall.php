<?php
// Ensure WordPress is uninstalling
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

require_once plugin_dir_path(__FILE__) . 'core/class-wsm-loader.php';
WSM\Core\WSM_Loader::register();

WSM\Core\WSM_Database::uninstall();
