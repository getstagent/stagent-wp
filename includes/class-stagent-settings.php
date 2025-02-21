<?php

if (!defined('ABSPATH')) {
    exit;
}

class Stagent_Settings {

    private $api;

    public function __construct($api_instance) {
        $this->api = $api_instance;
    }

    public function init() {
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'add_settings_menu']);
        add_action('admin_init', [$this, 'maybe_refresh_teams']);
        add_action('update_option_stagent_api_key', [$this, 'after_updating_api_key'], 10, 2);
        add_action('admin_notices', [$this, 'show_no_api_key_notice']);
        add_action('admin_notices', [$this, 'show_no_default_team_notice']);
    }

    // Register all settings
    public function register_settings() {
        register_setting('stagent_settings_group', 'stagent_api_key');
        register_setting('stagent_settings_group', 'stagent_default_team_id');
        register_setting('stagent_settings_group', 'stagent_show_past', [
            'type'    => 'boolean',
            'default' => true,
        ]);
        register_setting('stagent_settings_group', 'stagent_show_canceled', [
            'type'    => 'boolean',
            'default' => false,
        ]);
        register_setting('stagent_settings_group', 'stagent_show_flag', [
            'type'    => 'boolean',
            'default' => true,
        ]);
        register_setting('stagent_settings_group', 'stagent_dark_mode', [
            'type'    => 'boolean',
            'default' => false,
        ]);
        register_setting('stagent_settings_group', 'stagent_booking_widget');
        register_setting('stagent_settings_group', 'stagent_enable_booking_widget', [
            'type'    => 'boolean',
            'default' => false,
        ]);
    }

    public function add_settings_menu() {
        add_options_page(
            'Stagent Settings',
            'Stagent',
            'manage_options',
            'stagent-settings',
            [$this, 'render_settings_page']
        );
    }

    // Render settings page
    public function render_settings_page() {
        $api_key         = get_option('stagent_api_key');
        $default_team_id = get_option('stagent_default_team_id');
        $show_past       = get_option('stagent_show_past');
        $show_canceled   = get_option('stagent_show_canceled');
        $show_flag       = get_option('stagent_show_flag');
        $dark_mode       = get_option('stagent_dark_mode');
        $booking_widget  = get_option('stagent_booking_widget');
        $enable_widget   = get_option('stagent_enable_booking_widget');

        // Load cached teams
        $cached_teams_json = get_option('stagent_cached_teams', '');
        $teams = $cached_teams_json ? json_decode($cached_teams_json, true) : [];

        // If no teams loaded yet an API key present, try to get teams
        if ($api_key && empty($teams)) {
            $teams = $this->api->fetch_teams();
            if (!empty($teams)) {
                update_option('stagent_cached_teams', json_encode($teams));
            }
        }

        Stagent_Template::render('stagent-settings-page.php', [
            'api_key'              => $api_key,
            'default_team_id'      => $default_team_id,
            'show_past'            => $show_past,
            'show_canceled'        => $show_canceled,
            'show_flag'            => $show_flag,
            'dark_mode'            => $dark_mode,
            'teams'                => $teams,
            'booking_widget'       => $booking_widget,
            'enable_booking_widget'=> $enable_widget,
        ]);
    }

    public function maybe_refresh_teams() {
        if (isset($_GET['stagent_refresh_teams']) && $_GET['stagent_refresh_teams'] === '1') {
            // Force refresh
            update_option('stagent_cached_teams', '');
            $teams = $this->api->fetch_teams();
            if (!empty($teams)) {
                update_option('stagent_cached_teams', json_encode($teams));
            }
            wp_safe_redirect(remove_query_arg('stagent_refresh_teams'));
            exit;
        }
    }

    public function after_updating_api_key($old_value, $new_value) {
        if (empty($new_value)) {
            // Only clear API-specific data
            update_option('stagent_default_team_id', '');
            update_option('stagent_cached_teams', '');
            return;
        }

        if ($new_value && $new_value !== $old_value) {
            // Only clear cached data, keep other settings intact
            update_option('stagent_cached_teams', '');

            // Keep the default team ID if it exists, otherwise reset
            $current_team_id = get_option('stagent_default_team_id');
            if (empty($current_team_id)) {
                update_option('stagent_default_team_id', '');
            }

            // Get and cache teams
            $teams = $this->api->fetch_teams();
            if (!empty($teams)) {
                update_option('stagent_cached_teams', json_encode($teams));
            } else {
                return;
            }

            // Set default team if only one is available
            if (count($teams) === 1 && !empty($teams[0]['id'])) {
                update_option('stagent_default_team_id', $teams[0]['id']);
            } else {
                $latest_team_id = $this->api->fetch_latest_team_id();
                if ($latest_team_id) {
                    update_option('stagent_default_team_id', $latest_team_id);
                }
            }
        }
    }

    // Admin notice for setting API key
    public function show_no_api_key_notice() {
        $api_key = get_option('stagent_api_key');
        if (empty($api_key)) {
            echo '<div class="notice notice-warning"><p>To start using Stagent, you need to ';
            echo '<a href="' . esc_url(admin_url('options-general.php?page=stagent-settings')) . '">set a personal access token</a>.</p></div>';
        }
    }

    // Admin notice for setting default team
    public function show_no_default_team_notice() {
        $api_key = get_option('stagent_api_key');
        $default_team_id = get_option('stagent_default_team_id');
        if (empty($default_team_id) && $api_key) {
            echo '<div class="notice notice-warning"><p>No default team selected. ';
            echo '<a href="' . esc_url(admin_url('options-general.php?page=stagent-settings')) . '">Set a default team</a>.</p></div>';
        }
    }
}