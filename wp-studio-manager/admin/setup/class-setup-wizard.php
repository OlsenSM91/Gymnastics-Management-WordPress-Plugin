<?php
namespace WSM\Admin\Setup;

class Setup_Wizard {
    public static function register() {
        add_action('admin_menu', [__CLASS__, 'add_menu']);
    }

    public static function add_menu() {
        add_menu_page(
            'Studio Manager Setup',
            'Studio Setup',
            'manage_options',
            'wsm-setup',
            [__CLASS__, 'render']
        );
    }

    public static function render() {
        echo '<div class="wrap"><h1>WP Studio Manager Setup Wizard</h1>';
        echo '<p>This wizard will guide you through configuring the plugin for your industry.</p>';
        echo '</div>';
    }
}
