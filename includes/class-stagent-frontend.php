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
        add_action('wp_footer', [$this, 'output_booking_widget'], 20);
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

        $allowed_tags = [
            'script' => [
                'src'    => true,
                'defer'  => true,
            ],
        ];

        echo wp_kses($widget, $allowed_tags); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped via wp_kses
    }
}