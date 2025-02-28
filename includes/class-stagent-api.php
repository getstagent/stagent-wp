<?php
/**
 * Handles API interactions with the Stagent service.
 *
 * @package Stagent
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stagent_API class
 *
 * Manages API requests and responses for the Stagent plugin.
 */
class Stagent_API {

    /**
     * Initialize the API class.
     */
    public function init() {
        add_action('wp_ajax_stagent_fetch_artists', [$this, 'ajax_fetch_artists']);
    }

    /**
     * Make an API request to Stagent.
     *
     * @param string $endpoint The API endpoint (e.g., 'teams').
     * @param string $method   HTTP method (GET or POST). Default: 'GET'.
     * @param array  $args     Additional request arguments.
     * @return array|WP_Error Response data or error object.
     */
    public function request($endpoint, $method = 'GET', $args = []) {
        $api_key = get_option('stagent_api_key');
        if (empty($api_key)) {
            return new WP_Error('no_api_key', esc_html__('No API key set.', 'stagent'));
        }

        $endpoint = ltrim(sanitize_text_field($endpoint), '/');
        $url = esc_url_raw(rtrim(STAGENT_API_URL, '/') . '/' . $endpoint);

        $default_args = [
            'headers' => [
                'Authorization' => 'Bearer ' . sanitize_text_field($api_key),
                'Accept'        => 'application/json',
            ],
            'timeout'     => 8,
            'redirection' => 0,
            'sslverify'   => STAGENT_DEVELOPMENT_MODE ? false : true,
        ];

        $request_args = wp_parse_args($args, $default_args);

        switch (strtoupper($method)) {
            case 'GET':
                $response = wp_remote_get($url, $request_args);
                break;
            case 'POST':
                $response = wp_remote_post($url, $request_args);
                break;
            default:
                $response = new WP_Error('invalid_method', esc_html__('Invalid request method: ', 'stagent') . esc_html($method));
                break;
        }

        return $response;
    }

    /**
     * Fetch all teams from the Stagent API.
     *
     * @return array List of teams or empty array on failure.
     */
    public function fetch_teams() {
        $response = $this->request('teams', 'GET');
        if (is_wp_error($response)) {
            return [];
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 300) {
            return [];
        }

        $decoded = json_decode(wp_remote_retrieve_body($response), true);
        if (!isset($decoded['data']) || !is_array($decoded['data'])) {
            return [];
        }

        $teams_min = [];
        foreach ($decoded['data'] as $team) {
            $teams_min[] = [
                'id'   => sanitize_text_field($team['id'] ?? ''),
                'name' => sanitize_text_field($team['name'] ?? ''),
                'type' => sanitize_text_field($team['type'] ?? ''),
            ];
        }
        return $teams_min;
    }

    /**
     * Fetch the latest team ID for the current user.
     *
     * @return string|null Latest team ID or null on failure.
     */
    public function fetch_latest_team_id() {
        $response = $this->request('me', 'GET');
        if (is_wp_error($response)) {
            return null;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 300) {
            return null;
        }

        $decoded = json_decode(wp_remote_retrieve_body($response), true);
        return isset($decoded['data']['latest_team_id']) ? sanitize_text_field($decoded['data']['latest_team_id']) : null;
    }

    /**
     * Fetch artists for a specific team if it's a booking agency.
     *
     * @param string $team_id The team ID.
     * @return array|WP_Error List of artists or error object.
     */
    public function fetch_artists($team_id) {
        $team_id = sanitize_text_field($team_id);
        if (empty($team_id)) {
            return new WP_Error('no_team_id', esc_html__('No team ID provided.', 'stagent'));
        }

        $cached_teams_json = get_option('stagent_cached_teams', '');
        $teams = $cached_teams_json ? json_decode($cached_teams_json, true) : [];

        $is_agency = false;
        foreach ((array) $teams as $team) {
            if ($team['id'] === $team_id && $team['type'] === 'booking_agency') {
                $is_agency = true;
                break;
            }
        }

        if (!$is_agency) {
            return [];
        }

        $response = $this->request("teams/{$team_id}/artists", 'GET');
        if (is_wp_error($response)) {
            return [];
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        if ($code < 200 || $code >= 300) {
            return [];
        }

        $decoded = json_decode($body, true);
        if (!isset($decoded['data']) || !is_array($decoded['data'])) {
            return [];
        }

        $artists = array_map(static function ($artist) {
            return [
                'id'   => sanitize_text_field($artist['id'] ?? ''),
                'name' => sanitize_text_field($artist['name'] ?? ''),
            ];
        }, $decoded['data']);

        return $artists;
    }

    /**
     * Fetch bookings for a team with specified options.
     *
     * @param string $team_id   The team ID.
     * @param array  $artists   List of artist IDs.
     * @param bool   $canceled  Include canceled bookings.
     * @param string $show      Show type (upcoming, past, all).
     * @param int    $page      Page number.
     * @param int    $per_page  Items per page.
     * @return array Bookings data or error array.
     */
    public function fetch_bookings($team_id, $artists = [], $canceled = false, $show = 'upcoming', $page = 1, $per_page = 10) {
        $team_id = sanitize_text_field($team_id);
        $artists = array_map('sanitize_text_field', (array) $artists);
        $canceled = (bool) $canceled;
        $show = in_array($show, ['upcoming', 'past', 'all']) ? $show : 'upcoming';
        $page = max(1, (int) $page);
        $per_page = max(1, (int) $per_page);

        $body = [
            'artists'  => implode(',', $artists),
            'canceled' => $canceled ? 'true' : 'false',
            'show'     => $show,
            'page'     => $page,
            'per_page' => $per_page,
        ];

        $response = $this->request("teams/{$team_id}/bookings", 'GET', ['body' => $body]);
        if (is_wp_error($response)) {
            return ['error' => esc_html($response->get_error_message())];
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 300) {
            return ['error' => esc_html__('Failed to fetch data (HTTP ', 'stagent') . $code . ')'];
        }

        return json_decode(wp_remote_retrieve_body($response), true) ?: [];
    }

    /**
     * Handle AJAX request to fetch artists.
     */
    public function ajax_fetch_artists() {
        // Check nonce for security instead of capability
        if (!isset($_GET['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['nonce'])), 'stagent_admin_nonce')) {
            wp_send_json_error('Unauthorized access: Invalid or missing nonce.');
            wp_die();
        }

        if (!isset($_GET['team_id'])) {
            wp_send_json_error('Missing team ID parameter.');
            wp_die();
        }

        $team_id = sanitize_text_field(wp_unslash($_GET['team_id']));
        $artists = $this->fetch_artists($team_id);

        if (is_wp_error($artists)) {
            wp_send_json_error($artists->get_error_message());
            wp_die();
        }

        if (empty($artists)) {
            wp_send_json_error('No artists found or API response was empty.');
            wp_die();
        }

        wp_send_json_success($artists);
        wp_die();
    }
}