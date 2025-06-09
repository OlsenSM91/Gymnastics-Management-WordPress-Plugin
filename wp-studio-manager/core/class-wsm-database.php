<?php
namespace WSM\Core;

/**
 * Handles creation and cleanup of the plugin's custom database tables.
 */
class WSM_Database {
    /**
     * Create or update all database tables for the plugin.
     */
    public static function install() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();

        $tables = [];

        // Families/Accounts
        $tables[] = "CREATE TABLE {$wpdb->prefix}wsm_families (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(191) NOT NULL,
            contact_info text NULL,
            PRIMARY KEY  (id)
        ) $charset;";

        // Participants/Students
        $tables[] = "CREATE TABLE {$wpdb->prefix}wsm_students (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            family_id bigint(20) unsigned NOT NULL,
            first_name varchar(191) NOT NULL,
            last_name varchar(191) NOT NULL,
            PRIMARY KEY  (id),
            KEY family_id (family_id)
        ) $charset;";

        // Instructors/Coaches
        $tables[] = "CREATE TABLE {$wpdb->prefix}wsm_instructors (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            first_name varchar(191) NOT NULL,
            last_name varchar(191) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset;";

        // Class definitions
        $tables[] = "CREATE TABLE {$wpdb->prefix}wsm_classes (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            title varchar(191) NOT NULL,
            description text NULL,
            PRIMARY KEY  (id)
        ) $charset;";

        // Enrollments
        $tables[] = "CREATE TABLE {$wpdb->prefix}wsm_enrollments (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            student_id bigint(20) unsigned NOT NULL,
            class_id bigint(20) unsigned NOT NULL,
            enrollment_date datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY student_id (student_id),
            KEY class_id (class_id)
        ) $charset;";

        // Schedule instances
        $tables[] = "CREATE TABLE {$wpdb->prefix}wsm_schedules (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            class_id bigint(20) unsigned NOT NULL,
            start_time datetime NOT NULL,
            end_time datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY class_id (class_id)
        ) $charset;";

        // Payment transactions
        $tables[] = "CREATE TABLE {$wpdb->prefix}wsm_payments (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            family_id bigint(20) unsigned NOT NULL,
            amount decimal(10,2) NOT NULL DEFAULT 0,
            payment_date datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY family_id (family_id)
        ) $charset;";

        // Billing invoices
        $tables[] = "CREATE TABLE {$wpdb->prefix}wsm_invoices (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            family_id bigint(20) unsigned NOT NULL,
            total decimal(10,2) NOT NULL DEFAULT 0,
            issued_date datetime NOT NULL,
            due_date datetime NOT NULL,
            status varchar(50) NOT NULL DEFAULT 'unpaid',
            PRIMARY KEY  (id),
            KEY family_id (family_id)
        ) $charset;";

        // Communication log
        $tables[] = "CREATE TABLE {$wpdb->prefix}wsm_communications (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            family_id bigint(20) unsigned NOT NULL,
            message text NOT NULL,
            sent_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY family_id (family_id)
        ) $charset;";

        foreach ($tables as $sql) {
            dbDelta($sql);
        }
    }

    /**
     * Drop plugin tables on uninstall.
     */
    public static function uninstall() {
        global $wpdb;
        $table_names = [
            'wsm_families',
            'wsm_students',
            'wsm_instructors',
            'wsm_classes',
            'wsm_enrollments',
            'wsm_schedules',
            'wsm_payments',
            'wsm_invoices',
            'wsm_communications',
        ];
        foreach ($table_names as $name) {
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$name}");
        }
    }
}
