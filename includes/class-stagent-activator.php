<?php
/**
 * Handles plugin activation tasks for the Stagent plugin.
 *
 * @package Stagent
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stagent_Activator class
 *
 * Manages tasks executed when the plugin is activated.
 */
class Stagent_Activator {

    /**
     * Runs on plugin activation.
     */
    public static function activate() {
        // Check WordPress version compatibility
        if (version_compare(get_bloginfo('version'), '5.0', '<')) {
            wp_die(
                esc_html__('Stagent requires WordPress 5.0 or higher to function properly.', 'stagent'),
                esc_html__('Plugin Activation Error', 'stagent'),
                ['back_link' => true]
            );
        }

        // Set default options if not already set
        $default_options = [
            'stagent_api_key'             => '',
            'stagent_default_team_id'     => '',
            'stagent_show_past'           => true,
            'stagent_show_canceled'       => false,
            'stagent_show_flag'           => true,
            'stagent_dark_mode'           => false,
            'stagent_booking_widget'      => '',
            'stagent_enable_booking_widget' => false,
        ];

        foreach ($default_options as $option_name => $default_value) {
            if (false === get_option($option_name)) {
                update_option($option_name, $default_value);
            }
        }

        // Set a transient to trigger a welcome notice
        set_transient('stagent_activation_notice', true, 30);
    }
}