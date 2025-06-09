<?php
/*
Plugin Name: WP Studio Manager
Description: Comprehensive studio management system for gymnastics programs.
Version: 1.0
Author: Steven Olsen
*/

if (!defined('ABSPATH')) {
    exit;
}
define("WSM_PLUGIN_DIR", plugin_dir_path(__FILE__));
define("WSM_PLUGIN_URL", plugin_dir_url(__FILE__));

// Autoloader and core classes
require_once WSM_PLUGIN_DIR . 'core/class-gm-loader.php';
require_once WSM_PLUGIN_DIR . 'core/class-gm-activator.php';
require_once WSM_PLUGIN_DIR . 'core/class-gm-deactivator.php';
require_once WSM_PLUGIN_DIR . 'core/class-gm-plugin.php';

use WSM\Core\GM_Loader;
use WSM\Core\GM_Activator;
use WSM\Core\GM_Deactivator;
use WSM\Core\GM_Plugin;

GM_Loader::register();

register_activation_hook(__FILE__, array('WSM\\Core\\GM_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('WSM\\Core\\GM_Deactivator', 'deactivate'));

// Function to create the custom role for coaches
function wsm_add_instructor_role() {
    add_role(
        'coach',
        __('Coach'),
        array(
            'read'         => true,  // True allows this capability
            'edit_posts'   => false,
            'delete_posts' => false,
        )
    );
}
add_action('init', 'wsm_add_instructor_role');

// Register custom post types and taxonomies
function wsm_register_custom_post_types() {
    // Coaches
    $coach_labels = array(
        'name' => 'Coaches',
        'singular_name' => 'Coach',
        'menu_name' => 'Coaches',
        'name_admin_bar' => 'Coach',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Coach',
        'new_item' => 'New Coach',
        'edit_item' => 'Edit Coach',
        'view_item' => 'View Coach',
        'all_items' => 'All Coaches',
        'search_items' => 'Search Coaches',
        'parent_item_colon' => 'Parent Coaches:',
        'not_found' => 'No coaches found.',
        'not_found_in_trash' => 'No coaches found in Trash.'
    );

    $coach_args = array(
        'labels' => $coach_labels,
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'custom-fields'),
        'capability_type' => 'post',
        'rewrite' => array('slug' => 'coaches')
    );

    register_post_type('wsm_instructor', $coach_args);

    // Parents
    $parent_labels = array(
        'name' => 'Parents',
        'singular_name' => 'Parent',
        'menu_name' => 'Parents',
        'name_admin_bar' => 'Parent',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Parent',
        'new_item' => 'New Parent',
        'edit_item' => 'Edit Parent',
        'view_item' => 'View Parent',
        'all_items' => 'All Parents',
        'search_items' => 'Search Parents',
        'parent_item_colon' => 'Parent Parents:',
        'not_found' => 'No parents found.',
        'not_found_in_trash' => 'No parents found in Trash.'
    );

    $parent_args = array(
        'labels' => $parent_labels,
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'custom-fields'),
        'capability_type' => 'post',
        'rewrite' => array('slug' => 'parents')
    );

    register_post_type('wsm_family', $parent_args);

    // Classes
    $class_labels = array(
        'name' => 'Classes',
        'singular_name' => 'Class',
        'menu_name' => 'Classes',
        'name_admin_bar' => 'Class',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Class',
        'new_item' => 'New Class',
        'edit_item' => 'Edit Class',
        'view_item' => 'View Class',
        'all_items' => 'All Classes',
        'search_items' => 'Search Classes',
        'parent_item_colon' => 'Parent Classes:',
        'not_found' => 'No classes found.',
        'not_found_in_trash' => 'No classes found in Trash.'
    );

    $class_args = array(
        'labels' => $class_labels,
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'custom-fields'),
        'capability_type' => 'post',
        'rewrite' => array('slug' => 'classes')
    );

    register_post_type('wsm_session', $class_args);

    // Levels (as a custom taxonomy)
    $level_labels = array(
        'name' => 'Levels',
        'singular_name' => 'Level',
        'search_items' => 'Search Levels',
        'all_items' => 'All Levels',
        'parent_item' => 'Parent Level',
        'parent_item_colon' => 'Parent Level:',
        'edit_item' => 'Edit Level',
        'update_item' => 'Update Level',
        'add_new_item' => 'Add New Level',
        'new_item_name' => 'New Level Name',
        'menu_name' => 'Levels'
    );

    $level_args = array(
        'hierarchical' => true,
        'labels' => $level_labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'level')
    );

    register_taxonomy('wsm_level', array('wsm_session'), $level_args);
}
add_action('init', 'wsm_register_custom_post_types');

// Run plugin
GM_Plugin::instance()->run();
?>
