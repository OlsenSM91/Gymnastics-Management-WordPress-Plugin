<?php
namespace WSM\Core;

class GM_Plugin {
    private static $instance;

    private function __construct() {}

    public static function instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function run() {
        require_once WSM_PLUGIN_DIR . 'admin/controllers/admin-menu.php';
        require_once WSM_PLUGIN_DIR . 'admin/controllers/coaches.php';
        require_once WSM_PLUGIN_DIR . 'admin/controllers/parents.php';
        require_once WSM_PLUGIN_DIR . 'admin/controllers/levels.php';
        require_once WSM_PLUGIN_DIR . 'admin/controllers/classes.php';
        require_once WSM_PLUGIN_DIR . 'admin/setup/class-setup-wizard.php';

        \WSM\Admin\Setup\Setup_Wizard::register();
    }
}
