<?php
/**
 * Plugin Name: Stagent
 * Plugin URI: https://stagent.com
 * Description: Displays bookings from Stagent API in WordPress.
 * Version: 0.2.3
 * Author: StagentArtwin B.V.
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: stagent
 * Requires at least: 5.0
 * Requires PHP: 7.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('STAGENT_VERSION', '0.2.3');
define('STAGENT_API_URL', 'https://stagent.com/api/v2');
define('STAGENT_DEVELOPMENT_MODE', false);

define('STAGENT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('STAGENT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoload classes (optional improvement)
require_once STAGENT_PLUGIN_DIR . 'includes/class-stagent-activator.php';
require_once STAGENT_PLUGIN_DIR . 'includes/class-stagent-deactivator.php';
require_once STAGENT_PLUGIN_DIR . 'includes/class-stagent-plugin.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, ['Stagent_Activator', 'activate']);
register_deactivation_hook(__FILE__, ['Stagent_Deactivator', 'deactivate']);

// Enqueue front-end scripts and styles
function stagent_enqueue_frontend_scripts() {
    wp_enqueue_style('stagent-frontend', STAGENT_PLUGIN_URL . 'assets/css/stagent.css', [], STAGENT_VERSION);
    wp_enqueue_script('stagent-frontend', STAGENT_PLUGIN_URL . 'assets/js/stagent.js', [], STAGENT_VERSION, true);

    // Localize script with secure AJAX URL
    wp_localize_script('stagent-frontend', 'stagentData', [
        'ajaxUrl' => esc_url(admin_url('admin-ajax.php')),
        'nonce'   => wp_create_nonce('stagent_frontend_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'stagent_enqueue_frontend_scripts', 10);

// Enqueue admin scripts and styles
function stagent_enqueue_admin_scripts($hook) {
    // Only enqueue on plugin-specific pages for performance
    if (!in_array($hook, ['settings_page_stagent-settings'], true)) {
        return;
    }

    wp_enqueue_script('stagent-admin', STAGENT_PLUGIN_URL . 'assets/js/stagent-admin.js', [], STAGENT_VERSION, true);

    // Localize script with secure AJAX URL and nonce
    wp_localize_script('stagent-admin', 'stagentData', [
        'ajaxUrl' => esc_url(admin_url('admin-ajax.php')),
        'nonce'   => wp_create_nonce('stagent_admin_nonce'),
    ]);
}
add_action('admin_enqueue_scripts', 'stagent_enqueue_admin_scripts', 10, 1);

// Boot the plugin
function stagent_bootstrap_plugin() {
    static $plugin_instance = null;

    if (null === $plugin_instance) {
        $plugin_instance = new Stagent_Plugin();
        $plugin_instance->init();
    }

    return $plugin_instance; // Singleton pattern
}
add_action('plugins_loaded', 'stagent_bootstrap_plugin', 10);
