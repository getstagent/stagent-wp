<?php
/**
 * Handles front-end functionality for the Stagent plugin.
 *
 * @package Stagent
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stagent_Frontend class
 *
 * Manages front-end features such as rendering the booking widget.
 */
class Stagent_Frontend {

    /**
     * Initialize front-end hooks.
     */
    public function init() { // Changed to non-static for consistency
        add_action('wp_footer', [$this, 'output_booking_widget']);
    }

    /**
     * Outputs the booking widget script in the footer if enabled.
     */
    public function output_booking_widget() {
        // Skip if in admin, feed, or not main query for performance
        if (is_admin() || is_feed() || !is_main_query()) {
            return;
        }

        $enabled = get_option('stagent_enable_booking_widget', false);
        $widget  = get_option('stagent_booking_widget', '');

        if (!$enabled || empty($widget)) {
            return;
        }

        wp_register_script('stagent-booking-widget', $widget, [], STAGENT_VERSION, true);

        add_filter('script_loader_tag', function($tag, $handle) {
            if ('stagent-booking-widget' === $handle && false === strpos($tag, 'defer')) {
                $tag = str_replace(' src', ' defer src', $tag);
            }
            return $tag;
        }, 10, 2);

        wp_enqueue_script('stagent-booking-widget');
    }
}