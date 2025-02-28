<?php
/**
 * Manages settings and admin interface for the Stagent plugin.
 *
 * @package Stagent
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stagent_Settings class
 *
 * Handles plugin settings registration, admin menu, and related functionality.
 */
class Stagent_Settings {

    /**
     * Instance of Stagent_API.
     *
     * @var Stagent_API
     */
    private $api;

    /**
     * Constructor.
     *
     * @param Stagent_API $api_instance The API instance.
     */
    public function __construct($api_instance) {
        $this->api = $api_instance;
    }

    /**
     * Initialize settings hooks.
     */
    public function init() {
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'add_settings_menu']);
        add_action('admin_init', [$this, 'maybe_refresh_teams']);
        add_action('update_option_stagent_api_key', [$this, 'after_updating_api_key'], 10, 2);
        add_action('admin_notices', [$this, 'show_welcome_notice']);
        add_action('admin_notices', [$this, 'show_no_default_team_notice']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_settings_scripts']);
    }

    /**
     * Register all plugin settings.
     */
    public function register_settings() {
        register_setting('stagent_settings_group', 'stagent_api_key', [ // phpcs:ignore PluginCheck.CodeAnalysis.SettingSanitization.register_settingDynamic -- Sanitized via sanitize_text_field
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);
        register_setting('stagent_settings_group', 'stagent_default_team_id', [ // phpcs:ignore PluginCheck.CodeAnalysis.SettingSanitization.register_settingDynamic -- Sanitized via sanitize_text_field
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);
        register_setting('stagent_settings_group', 'stagent_show_past', [ // phpcs:ignore PluginCheck.CodeAnalysis.SettingSanitization.register_settingDynamic -- Sanitized via sanitize_boolean
            'type' => 'boolean',
            'sanitize_callback' => [$this, 'sanitize_boolean'],
            'default' => true,
        ]);
        register_setting('stagent_settings_group', 'stagent_show_canceled', [ // phpcs:ignore PluginCheck.CodeAnalysis.SettingSanitization.register_settingDynamic -- Sanitized via sanitize_boolean
            'type' => 'boolean',
            'sanitize_callback' => [$this, 'sanitize_boolean'],
            'default' => false,
        ]);
        register_setting('stagent_settings_group', 'stagent_show_flag', [ // phpcs:ignore PluginCheck.CodeAnalysis.SettingSanitization.register_settingDynamic -- Sanitized via sanitize_boolean
            'type' => 'boolean',
            'sanitize_callback' => [$this, 'sanitize_boolean'],
            'default' => true,
        ]);
        register_setting('stagent_settings_group', 'stagent_dark_mode', [ // phpcs:ignore PluginCheck.CodeAnalysis.SettingSanitization.register_settingDynamic -- Sanitized via sanitize_boolean
            'type' => 'boolean',
            'sanitize_callback' => [$this, 'sanitize_boolean'],
            'default' => false,
        ]);
        register_setting('stagent_settings_group', 'stagent_booking_widget', [ // phpcs:ignore PluginCheck.CodeAnalysis.SettingSanitization.register_settingDynamic -- Sanitized via validate_booking_widget
            'type' => 'string',
            'sanitize_callback' => [$this, 'validate_booking_widget'],
            'default' => '',
        ]);
        register_setting('stagent_settings_group', 'stagent_enable_booking_widget', [ // phpcs:ignore PluginCheck.CodeAnalysis.SettingSanitization.register_settingDynamic -- Sanitized via sanitize_boolean
            'type' => 'boolean',
            'sanitize_callback' => [$this, 'sanitize_boolean'],
            'default' => false,
        ]);
        register_setting('stagent_settings_group', 'stagent_cached_teams', [ // phpcs:ignore PluginCheck.CodeAnalysis.SettingSanitization.register_settingDynamic -- Sanitized via sanitize_json
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_json'],
            'default' => '',
        ]);
    }

    /**
     * Sanitize boolean values.
     *
     * @param mixed $value The value to sanitize.
     * @return bool
     */
    public function sanitize_boolean($value) {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Sanitize JSON strings.
     *
     * @param mixed $value The value to sanitize.
     * @return string
     */
    public function sanitize_json($value) {
        if (empty($value)) {
            return '';
        }
        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return '';
        }
        return wp_json_encode($decoded); // Re-encode to ensure consistency
    }

    /**
     * Validate booking widget script.
     *
     * @param string $input The input script value.
     * @return string The validated script or previous value if invalid.
     */
    public function validate_booking_widget($input) {
        $input = trim($input); // Remove leading/trailing whitespace
        if (empty($input)) {
            return ''; // Allow empty input to clear the field
        }

        // Flexible regex: allows optional attributes and whitespace
        $pattern = '/^<script\s+(?:[^>]*?\s+)?src=["\']https:\/\/stagent\.(com|test)\/widget\/widget\.js\?signature=[^"\']+["\'](?:\s+[^>]*)?(?:\s+defer)?\s*><\/script>$/i';

        if (!preg_match($pattern, $input)) {
            add_settings_error(
                'stagent_booking_widget',
                'invalid_widget_script',
                esc_html__('The booking request widget script is invalid. Please paste the exact <script> tag from Stagent.', 'stagent'),
                'error'
            );
            return get_option('stagent_booking_widget', ''); // Return previous value if invalid
        }

        return $input;
    }

    /**
     * Add settings menu page.
     */
    public function add_settings_menu() {
        $hook = add_options_page(
            esc_html__('Stagent', 'stagent'),
            esc_html__('Stagent', 'stagent'),
            'manage_options',
            'stagent-settings',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Enqueue settings page scripts.
     *
     * @param string $hook The current admin page hook.
     */
    public function enqueue_settings_scripts($hook) {
        if ($hook !== 'settings_page_stagent-settings') {
            return;
        }

        wp_enqueue_script(
            'stagent-settings-js',
            STAGENT_PLUGIN_URL . 'assets/js/stagent-settings.js',
            ['jquery'],
            STAGENT_VERSION,
            true
        );

        wp_localize_script(
            'stagent-settings-js',
            'stagentSettings',
            [
                'nonce' => wp_create_nonce('stagent_settings_nonce'),
            ]
        );
    }

    /**
     * Render the settings page with tabs.
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'stagent'));
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified immediately
        $active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'settings';
        $allowed_tabs = ['settings', 'shortcode'];
        $active_tab = in_array($active_tab, $allowed_tabs, true) ? $active_tab : 'settings';

        ?>
            <div class="wrap">
                <h2 class="nav-tab-wrapper">
                    <a href="<?php echo esc_url(admin_url('options-general.php?page=stagent-settings&tab=settings')); ?>" class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                        <?php esc_html_e('Settings', 'stagent'); ?>
                    </a>
                    <a href="<?php echo esc_url(admin_url('options-general.php?page=stagent-settings&tab=shortcode')); ?>" class="nav-tab <?php echo $active_tab === 'shortcode' ? 'nav-tab-active' : ''; ?>">
                        <?php esc_html_e('Shortcode Generator', 'stagent'); ?>
                    </a>
                </h2>

                <?php
                    if ($active_tab === 'settings') {
                        $this->render_settings_tab();
                    } else {
                        $this->render_shortcode_generator_tab();
                    }
                ?>
            </div>
        <?php
    }

    /**
     * Render the settings tab.
     */
    private function render_settings_tab() {
        $api_key         = get_option('stagent_api_key', '');
        $default_team_id = get_option('stagent_default_team_id', '');
        $show_past       = get_option('stagent_show_past', true);
        $show_canceled   = get_option('stagent_show_canceled', false);
        $show_flag       = get_option('stagent_show_flag', true);
        $dark_mode       = get_option('stagent_dark_mode', false);
        $booking_widget  = get_option('stagent_booking_widget', '');
        $enable_widget   = get_option('stagent_enable_booking_widget', false);

        $cached_teams_json = get_option('stagent_cached_teams', '');
        $teams = $cached_teams_json ? json_decode($cached_teams_json, true) : [];

        if ($api_key && empty($teams)) {
            $teams = $this->api->fetch_teams();
            if (!empty($teams)) {
                update_option('stagent_cached_teams', wp_json_encode($teams));
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
            'enable_booking_widget' => $enable_widget,
        ]);
    }

    /**
     * Render the shortcode generator tab.
     */
    private function render_shortcode_generator_tab() {
        $cached_teams_json = get_option('stagent_cached_teams', '');
        $teams = $cached_teams_json ? json_decode($cached_teams_json, true) : [];

        Stagent_Template::render('stagent-shortcode-generator-page.php', [
            'teams' => $teams,
        ]);
    }

    /**
     * Handle team refresh action.
     */
    public function maybe_refresh_teams() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified immediately
        if (empty($_GET['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['nonce'])), 'stagent_refresh_teams')) {
            return;
        }

        $refresh_teams = isset($_GET['stagent_refresh_teams']) ? sanitize_text_field(wp_unslash($_GET['stagent_refresh_teams'])) : '';
        if ($refresh_teams !== '1') {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to refresh teams.', 'stagent'));
        }

        update_option('stagent_cached_teams', '');
        $teams = $this->api->fetch_teams();
        if (!empty($teams)) {
            update_option('stagent_cached_teams', wp_json_encode($teams));
        }

        wp_safe_redirect(remove_query_arg(['stagent_refresh_teams', 'nonce']));
        exit;
    }

    /**
     * Handle API key update.
     *
     * @param string $old_value Previous API key value.
     * @param string $new_value New API key value.
     */
    public function after_updating_api_key($old_value, $new_value) {
        $new_value = sanitize_text_field($new_value);
        if (empty($new_value)) {
            update_option('stagent_default_team_id', '');
            update_option('stagent_cached_teams', '');
            return;
        }

        if ($new_value !== $old_value) {
            update_option('stagent_cached_teams', '');

            $current_team_id = get_option('stagent_default_team_id', '');
            if (empty($current_team_id)) {
                update_option('stagent_default_team_id', '');
            }

            $teams = $this->api->fetch_teams();
            if (!empty($teams)) {
                update_option('stagent_cached_teams', wp_json_encode($teams));
            } else {
                return;
            }

            if (count($teams) === 1 && !empty($teams[0]['id'])) {
                update_option('stagent_default_team_id', sanitize_text_field($teams[0]['id']));
            } else {
                $latest_team_id = $this->api->fetch_latest_team_id();
                if ($latest_team_id) {
                    update_option('stagent_default_team_id', sanitize_text_field($latest_team_id));
                }
            }
        }
    }

    /**
     * Display notice if no API key is set, including welcome message on activation.
     */
    public function show_welcome_notice() {
        if (get_transient('stagent_activation_notice')) {
            $message = sprintf(
            /* translators: %s: Link to settings page */
                esc_html__('Thanks for installing Stagent for WordPress. Configure the %s to get started.', 'stagent'),
                '<a href="' . esc_url(admin_url('options-general.php?page=stagent-settings')) . '">' . esc_html__('configure your API key', 'stagent') . '</a>'
            );
            echo wp_kses_post('<div class="notice notice-info is-dismissible"><p>' . $message . '</p></div>');
            delete_transient('stagent_activation_notice');
        }
    }

    /**
     * Display notice if no default team is set.
     */
    public function show_no_default_team_notice() {
        $api_key = get_option('stagent_api_key', '');
        $default_team_id = get_option('stagent_default_team_id', '');

        if (!empty($api_key) && empty($default_team_id)) {
            $message = sprintf(
            /* translators: %s: Link to settings page */
                esc_html__('The Stagent bookings shortcode is missing a default team. %s.', 'stagent'),
                '<a href="' . esc_url(admin_url('options-general.php?page=stagent-settings')) . '">' . esc_html__('Set a default team', 'stagent') . '</a>'
            );
            echo wp_kses_post('<div class="notice notice-warning"><p>' . $message . '</p></div>');
        }
    }
}