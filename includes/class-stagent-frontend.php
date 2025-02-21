<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Stagent_Frontend {

    // Initialize front-end hooks
    public static function init() {
        add_action( 'wp_footer', [ __CLASS__, 'output_booking_widget' ] );
    }

    // Renders the booking widget at the end of the page
    public static function output_booking_widget() {
        // Only output on the front-end
        if ( is_admin() ) {
            return;
        }

        $enabled = get_option( 'stagent_enable_booking_widget' );
        $widget  = get_option( 'stagent_booking_widget' );

        if ( $enabled && ! empty( $widget ) ) {
            $allowed_tags = [
                'script' => [
                    'src'     => true,
                    'defer'   => true,
                    'async'   => true,
                ]
            ];
            echo wp_kses( $widget, $allowed_tags );
        }
    }
}