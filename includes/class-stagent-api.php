<?php

if (!defined('ABSPATH')) {
    exit;
}

class Stagent_API {

    // API request to Stagent
    public function request($endpoint, $method = 'GET', $args = []) {
        $api_key = get_option('stagent_api_key');
        if (!$api_key) {
            return new WP_Error('no_api_key', 'No API key set.');
        }

        $endpoint = ltrim($endpoint, '/');
        $url = rtrim(STAGENT_API_URL, '/') . '/' . $endpoint;

        $default_args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Accept'        => 'application/json',
            ],
            'timeout'     => 8,
            'redirection' => 0,
            'sslverify'   => STAGENT_DEVELOPMENT_MODE == true,
        ];

        $request_args = array_merge($default_args, $args);

        switch (strtoupper($method)) {
            case 'GET':
                $response = wp_remote_get($url, $request_args);
                break;
            case 'POST':
                $response = wp_remote_post($url, $request_args);
                break;
            default:
                $response = new WP_Error('invalid_method', 'Invalid request method: ' . $method);
                break;
        }

        return $response;
    }

    // Get all teams
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
        if (!isset($decoded['data'])) {
            return [];
        }

        $teams_min = [];
        foreach ($decoded['data'] as $team) {
            $teams_min[] = [
                'id'   => $team['id'],
                'name' => isset($team['name']) ? $team['name'] : '',
                'type' => isset($team['type']) ? $team['type'] : '',
            ];
        }
        return $teams_min;
    }

    // Get latest team
    public function fetch_latest_team_id() {
        $response = $this->request('me', 'GET');
        if (is_wp_error($response)) {
            return null;
        }
        $decoded = json_decode(wp_remote_retrieve_body($response), true);
        return isset($decoded['data']['latest_team_id']) ? $decoded['data']['latest_team_id'] : null;
    }

    // Get all bookings, with some options
    public function fetch_bookings($team_id, $artists = [], $canceled = false, $show = 'upcoming', $page = 1, $per_page = 10) {
        $body = [
            'artists'  => implode(',', $artists),
            'canceled' => $canceled,
            'show'     => $show,
            'page'     => $page,
            'per_page' => $per_page,
        ];

        $response = $this->request("teams/{$team_id}/bookings", 'GET', ['body' => $body]);
        if (is_wp_error($response)) {
            return ['error' => $response->get_error_message()];
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 300) {
            return ['error' => 'Failed to fetch data (HTTP ' . $code . ')'];
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }
}