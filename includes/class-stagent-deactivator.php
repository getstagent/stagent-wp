<?php
/**
 * Handles plugin deactivation tasks for the Stagent plugin.
 *
 * @package Stagent
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stagent_Deactivator class
 *
 * Manages tasks executed when the plugin is deactivated.
 */
class Stagent_Deactivator {

    /**
     * List of options to delete on deactivation.
     *
     * @var array
     */
    private static $options_to_delete = [
        'stagent_default_team_id',
        'stagent_cached_teams',
        'stagent_show_past',
        'stagent_show_canceled',
        'stagent_show_flag',
        'stagent_dark_mode',
        'stagent_enable_booking_widget',
        'stagent_powered_by',
    ];

    /**
     * Runs on plugin deactivation.
     */
    public static function deactivate() {
        // Ensure only authorized users can trigger deactivation logic
        if (!current_user_can('activate_plugins')) {
            return;
        }

        // Delete all plugin options
        foreach (self::$options_to_delete as $option) {
            delete_option($option);
        }

        // Delete all transient cache
        self::delete_transients();

        // Flush rewrite rules (if custom endpoints were added)
        flush_rewrite_rules();
    }

    /**
     * Deletes all Stagent-related transients.
     */
    private static function delete_transients() {
        global $wpdb;

        // Use prepare to safely handle the query
        $transient_pattern = '_transient_stagent_%';
        $timeout_pattern = '_transient_timeout_stagent_%';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $transient_pattern
            )
        );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $timeout_pattern
            )
        );
    }
}