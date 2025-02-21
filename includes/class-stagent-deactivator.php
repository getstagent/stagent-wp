<?php

if (!defined('ABSPATH')) {
    exit;
}

class Stagent_Deactivator {

    public static function deactivate() {
        // Delete all settings
        delete_option('stagent_api_key');
        delete_option('stagent_default_team_id');
        delete_option('stagent_cached_teams');
        delete_option('stagent_show_past');
        delete_option('stagent_show_canceled');
        delete_option('stagent_show_flag');
        delete_option('stagent_dark_mode');
        delete_option('stagent_enable_booking_widget');
        delete_option('stagent_booking_widget');

        // Delete all transient cache
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_stagent_%'"
        );
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_stagent_%'"
        );
    }
}