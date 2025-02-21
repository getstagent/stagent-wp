<?php

if (!defined('ABSPATH')) {
    exit;
}

class Stagent_Plugin {

    // Initialize the plugin’s hooks and load required files
    public function init() {
        // Load all the classes
        require_once STAGENT_PLUGIN_DIR . 'includes/class-stagent-api.php';
        require_once STAGENT_PLUGIN_DIR . 'includes/class-stagent-frontend.php';
        require_once STAGENT_PLUGIN_DIR . 'includes/class-stagent-settings.php';
        require_once STAGENT_PLUGIN_DIR . 'includes/class-stagent-shortcodes.php';
        require_once STAGENT_PLUGIN_DIR . 'includes/class-stagent-template.php';

        // Instantiate classes
        $api         = new Stagent_API();
        $frontend    = new Stagent_Frontend();
        $settings    = new Stagent_Settings($api);
        $shortcodes  = new Stagent_Shortcodes($api);

        // Initialize classes
        $frontend->init();
        $settings->init();
        $shortcodes->init();
    }
}