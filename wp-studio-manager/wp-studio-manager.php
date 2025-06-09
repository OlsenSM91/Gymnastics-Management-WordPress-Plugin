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
require_once WSM_PLUGIN_DIR . 'core/class-wsm-loader.php';
require_once WSM_PLUGIN_DIR . 'core/class-wsm-activator.php';
require_once WSM_PLUGIN_DIR . 'core/class-wsm-deactivator.php';
require_once WSM_PLUGIN_DIR . 'core/class-wsm-plugin.php';
require_once WSM_PLUGIN_DIR . 'admin/controllers/class-participants-controller.php';

use WSM\Core\WSM_Loader;
use WSM\Core\WSM_Activator;
use WSM\Core\WSM_Deactivator;
use WSM\Core\WSM_Plugin;
use WSM\Includes\Industry_Configs\Industry_Config;

WSM_Loader::register();

register_activation_hook(__FILE__, array('WSM\\Core\\WSM_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('WSM\\Core\\WSM_Deactivator', 'deactivate'));

// Function to create the custom role for coaches
function gm_add_coach_role() {
    $label = Industry_Config::get_label('instructor_label');
    $role_slug = 'wsm_instructor';
    add_role(
        $role_slug,
        __( $label ),
        array(
            'read'         => true,
            'edit_posts'   => false,
            'delete_posts' => false,
        )
    );
}
add_action('init', 'gm_add_coach_role');

// Register custom post types and taxonomies
function gm_register_custom_post_types() {
    $config = Industry_Config::get_config();
    $instructor = $config['instructor_label'];
    $participant = $config['participant_label'];
    $session = $config['session_label'];

    // Coaches/Instructors
    $coach_labels = array(
        'name'               => $instructor . 's',
        'singular_name'      => $instructor,
        'menu_name'          => $instructor . 's',
        'name_admin_bar'     => $instructor,
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New ' . $instructor,
        'new_item'           => 'New ' . $instructor,
        'edit_item'          => 'Edit ' . $instructor,
        'view_item'          => 'View ' . $instructor,
        'all_items'          => 'All ' . $instructor . 's',
        'search_items'       => 'Search ' . $instructor . 's',
        'parent_item_colon'  => 'Parent ' . $instructor . 's:',
        'not_found'          => 'No ' . strtolower($instructor) . 's found.',
        'not_found_in_trash' => 'No ' . strtolower($instructor) . 's found in Trash.'
    );

    $coach_args = array(
        'labels'          => $coach_labels,
        'public'          => true,
        'has_archive'     => true,
        'supports'        => array('title', 'editor', 'custom-fields'),
        'capability_type' => 'post',
        'rewrite'         => array('slug' => sanitize_title($instructor . 's'))
    );

    register_post_type('gm_coach', $coach_args);

    // Participants (stored as parents for legacy)
    $parent_labels = array(
        'name'               => $participant . 's',
        'singular_name'      => $participant,
        'menu_name'          => $participant . 's',
        'name_admin_bar'     => $participant,
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New ' . $participant,
        'new_item'           => 'New ' . $participant,
        'edit_item'          => 'Edit ' . $participant,
        'view_item'          => 'View ' . $participant,
        'all_items'          => 'All ' . $participant . 's',
        'search_items'       => 'Search ' . $participant . 's',
        'parent_item_colon'  => 'Parent ' . $participant . 's:',
        'not_found'          => 'No ' . strtolower($participant) . 's found.',
        'not_found_in_trash' => 'No ' . strtolower($participant) . 's found in Trash.'
    );

    $parent_args = array(
        'labels'          => $parent_labels,
        'public'          => true,
        'has_archive'     => true,
        'supports'        => array('title', 'editor', 'custom-fields'),
        'capability_type' => 'post',
        'rewrite'         => array('slug' => sanitize_title($participant . 's'))
    );

    register_post_type('gm_parent', $parent_args);

    // Sessions/Classes
    $class_labels = array(
        'name'               => $session . 'es',
        'singular_name'      => $session,
        'menu_name'          => $session . 'es',
        'name_admin_bar'     => $session,
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New ' . $session,
        'new_item'           => 'New ' . $session,
        'edit_item'          => 'Edit ' . $session,
        'view_item'          => 'View ' . $session,
        'all_items'          => 'All ' . $session . 'es',
        'search_items'       => 'Search ' . $session . 'es',
        'parent_item_colon'  => 'Parent ' . $session . 'es:',
        'not_found'          => 'No ' . strtolower($session) . 'es found.',
        'not_found_in_trash' => 'No ' . strtolower($session) . 'es found in Trash.'
    );

    $class_args = array(
        'labels'          => $class_labels,
        'public'          => true,
        'has_archive'     => true,
        'supports'        => array('title', 'editor', 'custom-fields'),
        'capability_type' => 'post',
        'rewrite'         => array('slug' => sanitize_title($session . 'es'))
    );

    register_post_type('gm_class', $class_args);

    // Individual schedule instances
    $schedule_labels = array(
        'name'               => 'Schedules',
        'singular_name'      => 'Schedule',
        'menu_name'          => 'Schedules',
        'name_admin_bar'     => 'Schedule',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Schedule',
        'new_item'           => 'New Schedule',
        'edit_item'          => 'Edit Schedule',
        'view_item'          => 'View Schedule',
        'all_items'          => 'All Schedules',
        'search_items'       => 'Search Schedules',
        'parent_item_colon'  => 'Parent Schedules:',
        'not_found'          => 'No schedules found.',
        'not_found_in_trash' => 'No schedules found in Trash.'
    );

    $schedule_args = array(
        'labels'          => $schedule_labels,
        'public'          => false,
        'show_ui'         => true,
        'has_archive'     => false,
        'supports'        => array('title', 'editor', 'custom-fields'),
        'capability_type' => 'post',
        'rewrite'         => false
    );

    register_post_type('gm_schedule', $schedule_args);

    // Recital events for education centers
    $recital_args = array(
        'labels'          => array(
            'name' => 'Recitals',
            'singular_name' => 'Recital'
        ),
        'public'          => false,
        'show_ui'         => true,
        'supports'        => array('title', 'editor', 'custom-fields'),
        'capability_type' => 'post',
        'rewrite'         => false
    );
    register_post_type('gm_recital', $recital_args);

    // Messages sent to parents
    $message_args = array(
        'labels'          => array(
            'name' => 'Parent Messages',
            'singular_name' => 'Parent Message'
        ),
        'public'          => false,
        'show_ui'         => true,
        'supports'        => array('title', 'editor'),
        'capability_type' => 'post',
        'rewrite'         => false
    );
    register_post_type('gm_parent_message', $message_args);

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

    register_taxonomy('gm_level', array('gm_class'), $level_args);
}
add_action('init', 'gm_register_custom_post_types');

// Run plugin
WSM_Plugin::instance()->run();
?>
