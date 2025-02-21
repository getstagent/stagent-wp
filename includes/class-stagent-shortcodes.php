<?php

if (!defined('ABSPATH')) {
    exit;
}

// Manages the [stagent_bookings] shortcode
class Stagent_Shortcodes {

    private $api;

    public function __construct($api_instance) {
        $this->api = $api_instance;
    }

    public function init() {
        add_shortcode('stagent_bookings', [$this, 'display_bookings']);

        add_action('wp_ajax_stagent_load_more', [$this, 'ajax_load_more']);
        add_action('wp_ajax_nopriv_stagent_load_more', [$this, 'ajax_load_more']);
    }

    // The shortcode
    public function display_bookings($atts) {
        $default_team_id = get_option('stagent_default_team_id');
        $show_past = get_option('stagent_show_past', true);

        // Shortcode attributes
        $atts = shortcode_atts([
            'team'     => $default_team_id,
            'artists'  => '',
            'canceled' => '',
            'per_page' => 5,
            'show'     => 'all',
        ], $atts);

        if (empty($atts['team'])) {
            return 'Provide a team ID or set a default team in Stagent settings.';
        }

        // Parse the artists (as array)
        $artists  = (!empty($atts['artists'])) ? explode(',', $atts['artists']) : [];
        $per_page = max(1, (int) $atts['per_page']);

        // Determine whether to hide the artist name
        $hide_artist = false;
        // If there's only one artist passed via the shortcode, hide the artist
        if (count($artists) === 1) {
            $hide_artist = true;
        } else {
            // Otherwise, check the team type from cached teams
            $cached = get_option('stagent_cached_teams', '');
            if (!empty($cached)) {
                $teams = json_decode($cached, true);
                foreach ($teams as $team_info) {
                    if ($team_info['id'] == $atts['team'] && isset($team_info['type']) && $team_info['type'] === 'artist') {
                        $hide_artist = true;
                        break;
                    }
                }
            }
        }

        // Set default show mode
        $show = in_array($atts['show'], ['all', 'past', 'upcoming']) ? $atts['show'] : 'all';

        // Get canceled bookings, or not
        $canceled = ($atts['canceled'] === '')
            ? (bool) get_option('stagent_show_canceled')
            : filter_var($atts['canceled'], FILTER_VALIDATE_BOOLEAN);

        // Get upcoming bookings
        $upcoming = ($show !== 'past')
            ? $this->get_cached_bookings($atts['team'], $artists, $canceled, 'upcoming', 1, $per_page)
            : [];

        // Get past bookings
        $past = ($show_past && $show !== 'upcoming')
            ? $this->get_cached_bookings($atts['team'], $artists, $canceled, 'past', 1, $per_page)
            : [];

        // If there's an error, show it
        if (isset($upcoming['error'])) {
            return '<p>Error fetching upcoming bookings: ' . esc_html($upcoming['error']) . '</p>';
        }
        if (isset($past['error'])) {
            return '<p>Error fetching past bookings: ' . esc_html($past['error']) . '</p>';
        }

        // Pass all needed data to the container template
        $html = Stagent_Template::render('stagent-bookings-container.php', [
            'show'        => $show,
            'upcoming'    => $upcoming,
            'past'        => $past,
            'team'        => $atts['team'],
            'artists'     => $artists,
            'hide_artist' => $hide_artist,
            'per_page'    => $per_page,
        ], true);

        return $html;
    }

    // Fetch bookings with transient cache layer
    private function get_cached_bookings($team, $artists, $canceled, $show, $page, $per_page) {
        // If caching is disabled, always fetch from API
        if ( defined('STAGENT_DEVELOPMENT_MODE') && STAGENT_DEVELOPMENT_MODE === true ) {
            return $this->api->fetch_bookings($team, $artists, $canceled, $show, $page, $per_page);
        }

        // Create a unique cache key
        $cache_key = 'stagent_' . md5(implode('|', [
                $team,
                implode(',', $artists),
                $canceled,
                $show,
                $page,
                $per_page
            ]));

        // Try retrieving from cache
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        // Not cached, fetch from API
        $bookings = $this->api->fetch_bookings($team, $artists, $canceled, $show, $page, $per_page);

        // Store in transient if no error
        if (!isset($bookings['error'])) {
            // Cache for 15 minutes
            set_transient($cache_key, $bookings, 15 * MINUTE_IN_SECONDS);
        }

        return $bookings;
    }

    // Callback for load more
    public function ajax_load_more() {
        // If using a nonce, check it here
        $team     = isset($_POST['team']) ? sanitize_text_field($_POST['team']) : '';
        $show     = isset($_POST['show']) ? sanitize_text_field($_POST['show']) : 'upcoming';
        $page     = isset($_POST['page']) ? intval($_POST['page']) : 2;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;
        $artists  = [];
        $canceled = isset($_POST['canceled']) ? filter_var($_POST['canceled'], FILTER_VALIDATE_BOOLEAN) : false;

        if (!empty($_POST['artists'])) {
            $artists = array_map('sanitize_text_field', explode(',', $_POST['artists']));
        }

        if (empty($team)) {
            wp_send_json_error('No team provided.');
        }

        // Fetch next page with caching
        $bookings = $this->get_cached_bookings($team, $artists, $canceled, $show, $page, $per_page);
        if (isset($bookings['error'])) {
            wp_send_json_error($bookings['error']);
        }

        // Build HTML for the new booking items
        $html = '';
        if (!empty($bookings['data'])) {
            foreach ($bookings['data'] as $booking) {
                $html .= Stagent_Template::render('stagent-booking-item.php', [
                    'booking' => $booking,
                    'canceled' => isset($booking['is_canceled']) ? $booking['is_canceled'] : false,
                ], true);
            }
        }

        wp_send_json_success([
            'html'  => $html,
            'count' => !empty($bookings['data']) ? count($bookings['data']) : 0,
        ]);
    }
}