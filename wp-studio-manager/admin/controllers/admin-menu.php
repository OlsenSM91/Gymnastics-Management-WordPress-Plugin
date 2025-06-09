<?php
// Add admin menu
function gm_add_admin_menu() {
    add_menu_page('Gymnastics Management', 'Gym Management', 'manage_options', 'gym-management', 'gm_admin_page', 'dashicons-groups', 6);
    add_submenu_page('gym-management', 'Coaches', 'Coaches', 'manage_options', 'gym-coaches', 'gm_coaches_page');
    add_submenu_page('gym-management', 'Parents', 'Athletes', 'manage_options', 'gym-parents', 'gm_parents_page');
    add_submenu_page('gym-management', 'Levels', 'Levels', 'manage_options', 'gym-levels', 'gm_levels_page');
    add_submenu_page('gym-management', 'Classes', 'Classes', 'manage_options', 'gym-classes', 'gm_classes_page');
}
add_action('admin_menu', 'gm_add_admin_menu');

// Function to display the dashboard page with actionable tiles
function gm_admin_page() {
    echo '<h1>Gymnastics Management Dashboard</h1>';
    echo '<p>Welcome to the Gymnastics Management plugin. Use the menu on the left to navigate through the features.</p>';

    echo '<style>
            .gm-dashboard-tile {
                display: inline-block;
                width: 200px;
                margin: 10px;
                padding: 20px;
                text-align: center;
                background-color: #f1f1f1;
                border: 1px solid #ccc;
                border-radius: 4px;
                text-decoration: none;
                color: #000;
                font-weight: bold;
            }
            .gm-dashboard-tile:hover {
                background-color: #e1e1e1;
            }
          </style>';

    $actions = [
        'clear_athletes' => 'Clear Athletes',
        'clear_parents' => 'Clear Parents',
        'clear_coaches' => 'Clear Coaches',
        'clear_classes' => 'Clear Classes',
        'clear_all' => 'Clear All'
    ];

    $url_base = admin_url('admin.php?page=gym-management&action=');

    echo '<div class="gm-dashboard-tiles">';
    foreach ($actions as $action => $label) {
        echo '<a href="' . $url_base . $action . '" class="gm-dashboard-tile">' . $label . '</a>';
    }
    echo '</div>';
}

// Handle debug actions directly from the dashboard
function gm_handle_debug_actions() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_GET['page']) && $_GET['page'] === 'gym-management' && isset($_GET['action'])) {
        $action = $_GET['action'];
        if (in_array($action, ['clear_athletes', 'clear_parents', 'clear_coaches', 'clear_classes', 'clear_all'])) {
            switch ($action) {
                case 'clear_athletes':
                    gm_clear_athletes();
                    break;
                case 'clear_parents':
                    gm_clear_parents();
                    break;
                case 'clear_coaches':
                    gm_clear_coaches();
                    break;
                case 'clear_classes':
                    gm_clear_classes();
                    break;
                case 'clear_all':
                    gm_clear_all();
                    break;
            }

            wp_redirect(admin_url('admin.php?page=gym-management&success=true'));
            exit;
        }
    }
}
add_action('admin_init', 'gm_handle_debug_actions');

// Debug functions
function gm_clear_athletes() {
    $parents = get_posts(array(
        'post_type' => 'gm_parent',
        'post_status' => 'publish',
        'numberposts' => -1,
    ));
    foreach ($parents as $parent) {
        delete_post_meta($parent->ID, '_gm_parent_athletes');
    }
}

function gm_clear_parents() {
    $parents = get_posts(array(
        'post_type' => 'gm_parent',
        'post_status' => 'publish',
        'numberposts' => -1,
    ));
    foreach ($parents as $parent) {
        wp_delete_post($parent->ID, true);
    }
}

function gm_clear_coaches() {
    $coaches = get_posts(array(
        'post_type' => 'gm_coach',
        'post_status' => 'publish',
        'numberposts' => -1,
    ));
    foreach ($coaches as $coach) {
        wp_delete_post($coach->ID, true);
    }
}

function gm_clear_classes() {
    $classes = get_posts(array(
        'post_type' => 'gm_class',
        'post_status' => 'publish',
        'numberposts' => -1,
    ));
    foreach ($classes as $class) {
        wp_delete_post($class->ID, true);
    }
}

function gm_clear_all() {
    gm_clear_athletes();
    gm_clear_parents();
    gm_clear_coaches();
    gm_clear_classes();
}

// Enqueue admin scripts
function gm_enqueue_admin_scripts($hook_suffix) {
    if (strpos($hook_suffix, 'gym-') !== false) {
        wp_enqueue_script('gm-admin-script', WSM_PLUGIN_URL . 'assets/js/admin/admin.js', array('jquery'), '1.0', true);
        wp_enqueue_style('gm-classes-style', WSM_PLUGIN_URL . 'assets/css/admin/classes.css');

        if (strpos($hook_suffix, 'gym-classes') !== false) {
            wp_enqueue_script('gm-classes-script', WSM_PLUGIN_URL . 'assets/js/admin/classes.js', array('jquery'), '1.0', true);
            wp_localize_script('gm-classes-script', 'wsmClasses', array(
                'adminPostUrl' => admin_url('admin-post.php'),
                'adminAjaxUrl' => admin_url('admin-ajax.php'),
                'deleteNonce'  => wp_create_nonce('delete_class_nonce')
            ));
        }
    }
}
add_action('admin_enqueue_scripts', 'gm_enqueue_admin_scripts');
?>
