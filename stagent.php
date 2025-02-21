<?php
/**
 * Plugin Name: Stagent
 * Plugin URI: https://stagent.com
 * Description: Displays bookings from Stagent API in WordPress.
 * Version: v0.1.0
 * Author: StagentArtwin B.V.
 * Author URI: https://stagent.com
 */

if (!defined('ABSPATH')) {
    exit;
}

define('STAGENT_VERSION', 'v0.1.0');
define('STAGENT_API_URL', 'https://stagent.com/api/v2');
define('STAGENT_DEVELOPMENT_MODE', false);

define('STAGENT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('STAGENT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Require the activation/deactivation classes
require_once STAGENT_PLUGIN_DIR . 'includes/class-stagent-activator.php';
require_once STAGENT_PLUGIN_DIR . 'includes/class-stagent-deactivator.php';

// Register hooks for activation & deactivation
register_activation_hook(__FILE__, array('Stagent_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('Stagent_Deactivator', 'deactivate'));

// Enqueue front-end scripts and styles
function stagent_enqueue_scripts() {
    wp_enqueue_style(
        'stagent-css',
        STAGENT_PLUGIN_URL . 'assets/css/stagent.css',
        array(),
        STAGENT_VERSION
    );

    wp_register_script(
        'stagent-js',
        STAGENT_PLUGIN_URL . 'assets/js/stagent.js',
        array(),
        STAGENT_VERSION,
        true
    );

    wp_localize_script('stagent-js', 'stagentData', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
    ));

    wp_enqueue_script('stagent-js');
}
add_action('wp_enqueue_scripts', 'stagent_enqueue_scripts');

// Require main plugin class
require_once STAGENT_PLUGIN_DIR . 'includes/class-stagent-plugin.php';

// Boot Stagent plugin
function run_stagent_plugin() {
    $plugin = new Stagent_Plugin();
    $plugin->init();
}
run_stagent_plugin();