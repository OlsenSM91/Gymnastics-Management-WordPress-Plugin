<?php
namespace WSM\Admin\Controllers;

use WSM\Includes\Utilities\Industry_Helper;
use WSM\Includes\Industry_Configs\Industry_Config;

class Participants_Controller {
    public static function register() {
        add_action('admin_menu', [__CLASS__, 'add_menu']);
    }

    public static function add_menu() {
        add_menu_page(
            Industry_Config::get_label('participant_label', true),
            Industry_Config::get_label('participant_label', true),
            'manage_options',
            'wsm-participants',
            [__CLASS__, 'render_page']
        );
    }

    public static function render_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        echo '<div class="wrap">';
        echo '<h1>' . esc_html(Industry_Config::get_label('participant_label', true)) . '</h1>';
        echo '<form method="post">';
        Industry_Helper::render_fields('participant');
        submit_button('Save');
        echo '</form></div>';
    }
}

Participants_Controller::register();
