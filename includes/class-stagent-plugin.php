<?php
/**
 * Main plugin class for Stagent.
 *
 * Bootstraps the plugin by loading and initializing all components.
 *
 * @package Stagent
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stagent_Plugin class
 *
 * Central class responsible for initializing the Stagent plugin.
 */
class Stagent_Plugin {

    /**
     * Instance of Stagent_API.
     *
     * @var Stagent_API
     */
    private $api;

    /**
     * Instance of Stagent_Frontend.
     *
     * @var Stagent_Frontend
     */
    private $frontend;

    /**
     * Instance of Stagent_Settings.
     *
     * @var Stagent_Settings
     */
    private $settings;

    /**
     * Instance of Stagent_Shortcodes.
     *
     * @var Stagent_Shortcodes
     */
    private $shortcodes;

    /**
     * Initialize the plugin’s hooks and load required files.
     */
    public function init() {
        // Load required classes safely
        $this->load_dependencies();

        // Instantiate classes
        $this->api        = new Stagent_API();
        $this->frontend   = new Stagent_Frontend();
        $this->settings   = new Stagent_Settings($this->api);
        $this->shortcodes = new Stagent_Shortcodes($this->api);

        // Initialize components
        $this->initialize_components();

        // Allow extensions to hook in after initialization
        do_action('stagent_plugin_initialized', $this);
    }

    /**
     * Load all required class files.
     */
    private function load_dependencies() {
        $required_files = [
            STAGENT_PLUGIN_DIR . 'includes/class-stagent-api.php'        => 'Stagent_API',
            STAGENT_PLUGIN_DIR . 'includes/class-stagent-frontend.php'   => 'Stagent_Frontend',
            STAGENT_PLUGIN_DIR . 'includes/class-stagent-settings.php'   => 'Stagent_Settings',
            STAGENT_PLUGIN_DIR . 'includes/class-stagent-shortcodes.php' => 'Stagent_Shortcodes',
            STAGENT_PLUGIN_DIR . 'includes/class-stagent-template.php'   => 'Stagent_Template',
        ];

        foreach ($required_files as $file => $class) {
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }

    /**
     * Initialize all plugin components.
     */
    private function initialize_components() {
        $this->api->init();
        $this->frontend->init();
        $this->settings->init();
        $this->shortcodes->init();
    }

    /**
     * Get the API instance.
     *
     * @return Stagent_API
     */
    public function get_api() {
        return $this->api;
    }

    /**
     * Get the frontend instance.
     *
     * @return Stagent_Frontend
     */
    public function get_frontend() {
        return $this->frontend;
    }

    /**
     * Get the settings instance.
     *
     * @return Stagent_Settings
     */
    public function get_settings() {
        return $this->settings;
    }

    /**
     * Get the shortcodes instance.
     *
     * @return Stagent_Shortcodes
     */
    public function get_shortcodes() {
        return $this->shortcodes;
    }
}