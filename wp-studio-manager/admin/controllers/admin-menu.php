<?php
// Add admin menu
function wsm_add_admin_menu() {
    add_menu_page('Studio Management', 'Studio Management', 'manage_options', 'wsm-management', 'wsm_admin_page', 'dashicons-groups', 6);
    add_submenu_page('wsm-management', 'Instructors', 'Instructors', 'manage_options', 'wsm-instructors', 'wsm_instructors_page');
    add_submenu_page('wsm-management', 'Families', 'Participants', 'manage_options', 'wsm-families', 'wsm_families_page');
    add_submenu_page('wsm-management', 'Levels', 'Levels', 'manage_options', 'wsm-levels', 'wsm_levels_page');
    add_submenu_page('wsm-management', 'Sessions', 'Sessions', 'manage_options', 'wsm-sessions', 'wsm_sessions_page');
}
add_action('admin_menu', 'wsm_add_admin_menu');

// Function to display the dashboard page with actionable tiles
function wsm_admin_page() {
    echo '<h1>Studio Management Dashboard</h1>';
    echo '<p>Welcome to the Studio Management plugin. Use the menu on the left to navigate through the features.</p>';

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
        'clear_athletes' => 'Clear Participants',
        'clear_parents' => 'Clear Families',
        'clear_coaches' => 'Clear Instructors',
        'clear_classes' => 'Clear Sessions',
        'clear_all' => 'Clear All'
    ];

    $url_base = admin_url('admin.php?page=wsm-management&action=');

    echo '<div class="gm-dashboard-tiles">';
    foreach ($actions as $action => $label) {
        echo '<a href="' . $url_base . $action . '" class="gm-dashboard-tile">' . $label . '</a>';
    }
    echo '</div>';
}

// Handle debug actions directly from the dashboard
function wsm_handle_debug_actions() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_GET['page']) && $_GET['page'] === 'wsm-management' && isset($_GET['action'])) {
        $action = $_GET['action'];
        if (in_array($action, ['clear_athletes', 'clear_parents', 'clear_coaches', 'clear_classes', 'clear_all'])) {
            switch ($action) {
                case 'clear_athletes':
                    wsm_clear_participants();
                    break;
                case 'clear_parents':
                    wsm_clear_families();
                    break;
                case 'clear_coaches':
                    wsm_clear_instructors();
                    break;
                case 'clear_classes':
                    wsm_clear_sessions();
                    break;
                case 'clear_all':
                    wsm_clear_all();
                    break;
            }

            wp_redirect(admin_url('admin.php?page=wsm-management&success=true'));
            exit;
        }
    }
}
add_action('admin_init', 'wsm_handle_debug_actions');

// Debug functions
function wsm_clear_participants() {
    $parents = get_posts(array(
        'post_type' => 'wsm_family',
        'post_status' => 'publish',
        'numberposts' => -1,
    ));
    foreach ($parents as $parent) {
        delete_post_meta($parent->ID, '_wsm_family_athletes');
    }
}

function wsm_clear_families() {
    $parents = get_posts(array(
        'post_type' => 'wsm_family',
        'post_status' => 'publish',
        'numberposts' => -1,
    ));
    foreach ($parents as $parent) {
        wp_delete_post($parent->ID, true);
    }
}

function wsm_clear_instructors() {
    $coaches = get_posts(array(
        'post_type' => 'wsm_instructor',
        'post_status' => 'publish',
        'numberposts' => -1,
    ));
    foreach ($coaches as $coach) {
        wp_delete_post($coach->ID, true);
    }
}

function wsm_clear_sessions() {
    $classes = get_posts(array(
        'post_type' => 'wsm_session',
        'post_status' => 'publish',
        'numberposts' => -1,
    ));
    foreach ($classes as $class) {
        wp_delete_post($class->ID, true);
    }
}

function wsm_clear_all() {
    wsm_clear_participants();
    wsm_clear_families();
    wsm_clear_instructors();
    wsm_clear_sessions();
}

// Enqueue admin scripts
function wsm_enqueue_admin_scripts($hook_suffix) {
    if (strpos($hook_suffix, 'wsm-') !== false) {
        wp_enqueue_script('gm-admin-script', WSM_PLUGIN_URL . 'assets/js/admin/admin.js', array('jquery'), '1.0', true);
        wp_enqueue_style('gm-classes-style', WSM_PLUGIN_URL . 'assets/css/admin/classes.css');

        if (strpos($hook_suffix, 'wsm-sessions') !== false) {
            wp_enqueue_script('gm-classes-script', WSM_PLUGIN_URL . 'assets/js/admin/classes.js', array('jquery'), '1.0', true);
            wp_localize_script('gm-classes-script', 'wsmSessions', array(
                'adminPostUrl' => admin_url('admin-post.php'),
                'adminAjaxUrl' => admin_url('admin-ajax.php'),
                'deleteNonce'  => wp_create_nonce('delete_class_nonce')
            ));
        }
    }
}
add_action('admin_enqueue_scripts', 'wsm_enqueue_admin_scripts');
?>
