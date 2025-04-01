<?php
/**
 * Manages shortcodes for the Stagent plugin.
 *
 * @package Stagent
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stagent_Shortcodes class
 *
 * Handles the [stagent_bookings] shortcode and related AJAX functionality.
 */
class Stagent_Shortcodes {

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
     * Initialize shortcode and AJAX hooks.
     */
    public function init() {
        add_shortcode('stagent_bookings', [$this, 'display_bookings']);
        add_action('wp_ajax_stagent_load_more', [$this, 'ajax_load_more']);
        add_action('wp_ajax_nopriv_stagent_load_more', [$this, 'ajax_load_more']);
    }

    /**
     * Render the [stagent_bookings] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output for the bookings.
     */
    public function display_bookings($atts) {
        $default_team_id = get_option('stagent_default_team_id', '');
        $show_past = get_option('stagent_show_past', true);

        $atts = shortcode_atts([
            'team'     => $default_team_id,
            'artists'  => '',
            'canceled' => '',
            'show'     => 'all',
            'show_past' => '',
            'dark_mode' => '',
            'per_page' => 5,

        ], $atts, 'stagent_bookings');

        $team = sanitize_text_field($atts['team']);
        if (empty($team)) {
            return '<p>' . esc_html__('Provide a team ID or set a default team in Stagent settings.', 'stagent') . '</p>';
        }

        $artists = !empty($atts['artists']) ? array_map('sanitize_text_field', explode(',', $atts['artists'])) : [];
        $per_page = max(1, filter_var($atts['per_page'], FILTER_VALIDATE_INT, ['options' => ['default' => 5]]));
        $show = in_array($atts['show'], ['all', 'past', 'upcoming'], true) ? $atts['show'] : 'all';
        $canceled = $atts['canceled'] === '' ? (bool) get_option('stagent_show_canceled', false) : filter_var($atts['canceled'], FILTER_VALIDATE_BOOLEAN);
        $show_past = $atts['show_past'] === '' ? (bool) get_option('stagent_show_past', true) : filter_var($atts['show_past'], FILTER_VALIDATE_BOOLEAN);
        $dark_mode = $atts['dark_mode'] === '' ? (bool) get_option('stagent_dark_mode', false) : filter_var($atts['dark_mode'], FILTER_VALIDATE_BOOLEAN);

        $hide_artist = false;
        if (count($artists) === 1) {
            $hide_artist = true;
        } else {
            $cached = get_option('stagent_cached_teams', '');
            if (!empty($cached)) {
                $teams = json_decode($cached, true);
                if (is_array($teams)) {
                    foreach ($teams as $team_info) {
                        if ($team_info['id'] === $team && isset($team_info['type']) && $team_info['type'] === 'artist') {
                            $hide_artist = true;
                            break;
                        }
                    }
                }
            }
        }

        $upcoming = $show !== 'past' ? $this->get_cached_bookings($team, $artists, $canceled, 'upcoming', 1, $per_page) : [];
        $past = $show_past && $show !== 'upcoming' ? $this->get_cached_bookings($team, $artists, $canceled, 'past', 1, $per_page) : [];

        if (isset($upcoming['error'])) {
            return '<p>' . esc_html__('Error fetching upcoming bookings: ', 'stagent') . esc_html($upcoming['error']) . '</p>';
        }
        if (isset($past['error'])) {
            return '<p>' . esc_html__('Error fetching past bookings: ', 'stagent') . esc_html($past['error']) . '</p>';
        }

        return Stagent_Template::render('stagent-bookings-container.php', [
            'show'        => $show,
            'upcoming'    => $upcoming,
            'past'        => $past,
            'team'        => $team,
            'artists'     => $artists,
            'hide_artist' => $hide_artist,
            'dark_mode'   => $dark_mode,
            'show_past'   => $show_past,
            'per_page'    => $per_page,
            'canceled'    => $canceled,
        ], true);
    }

    /**
     * Fetch bookings with transient caching.
     *
     * @param string $team     Team ID.
     * @param array  $artists  Array of artist IDs.
     * @param bool   $canceled Include canceled bookings.
     * @param string $show     Show type (upcoming, past, all).
     * @param int    $page     Page number.
     * @param int    $per_page Items per page.
     * @return array Bookings data.
     */
    private function get_cached_bookings($team, $artists, $canceled, $show, $page, $per_page) {
        if (STAGENT_DEVELOPMENT_MODE) {
            return $this->api->fetch_bookings($team, $artists, $canceled, $show, $page, $per_page);
        }

        $cache_key = 'stagent_' . md5(implode('|', [
                $team,
                implode(',', $artists),
                $canceled ? '1' : '0',
                $show,
                $page,
                $per_page
            ]));

        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $bookings = $this->api->fetch_bookings($team, $artists, $canceled, $show, $page, $per_page);

        if (!isset($bookings['error'])) {
            set_transient($cache_key, $bookings, 15 * MINUTE_IN_SECONDS);
        }

        return $bookings;
    }

    /**
     * Handle AJAX load more request for bookings.
     */
    public function ajax_load_more() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'stagent_frontend_nonce')) {
            wp_send_json_error(esc_html__('Security check failed.', 'stagent'));
            wp_die();
        }

        $team = isset($_POST['team']) ? sanitize_text_field(wp_unslash($_POST['team'])) : '';
        $show = isset($_POST['show']) ? sanitize_text_field(wp_unslash($_POST['show'])) : 'upcoming';
        $page = isset($_POST['page']) ? filter_var(wp_unslash($_POST['page']), FILTER_VALIDATE_INT, ['options' => ['default' => 2]]) : 2;
        $per_page = isset($_POST['per_page']) ? filter_var(wp_unslash($_POST['per_page']), FILTER_VALIDATE_INT, ['options' => ['default' => 10]]) : 10;
        $canceled = isset($_POST['canceled']) ? filter_var(wp_unslash($_POST['canceled']), FILTER_VALIDATE_BOOLEAN) : false;
        $artists = [];

        if (!empty($_POST['artists'])) {
            $artists = array_map('sanitize_text_field', explode(',', sanitize_text_field(wp_unslash($_POST['artists']))));
        }

        if (empty($team)) {
            wp_send_json_error(esc_html__('No team provided.', 'stagent'));
            wp_die();
        }

        $show = in_array($show, ['upcoming', 'past', 'all'], true) ? $show : 'upcoming';
        $page = max(1, $page);
        $per_page = max(1, $per_page);

        $bookings = $this->get_cached_bookings($team, $artists, $canceled, $show, $page, $per_page);
        if (isset($bookings['error'])) {
            wp_send_json_error(esc_html($bookings['error']));
            wp_die();
        }

        $html = '';
        if (!empty($bookings['data']) && is_array($bookings['data'])) {
            foreach ($bookings['data'] as $booking) {
                $html .= Stagent_Template::render('stagent-booking-list-item.php', [
                    'booking'  => $booking,
                    'canceled' => isset($booking['is_canceled']) ? $booking['is_canceled'] : false,
                ], true);
            }
        }

        wp_send_json_success([
            'html'     => $html,
            'count'    => !empty($bookings['data']) ? count($bookings['data']) : 0,
            'has_more' => !empty($bookings['data']) && count($bookings['data']) === $per_page,
        ]);
        wp_die();
    }
}