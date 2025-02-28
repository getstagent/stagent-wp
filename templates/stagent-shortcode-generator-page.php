<?php
/**
 * Template: stagent-shortcode-generator-page.php
 *
 * Renders the Stagent plugin shortcode generator page in the WordPress admin.
 *
 * @package Stagent
 * @since Stagent v0.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Sanitize and validate variables passed from Stagent_Settings::render_shortcode_generator_tab
$teams = is_array($teams ?? []) ? $teams : [];
?>

<div class="wrap">
    <h2><?php esc_html_e('Shortcode Generator', 'stagent'); ?></h2>
    <div class="shortcode-generator">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><?php esc_html_e('Team', 'stagent'); ?></th>
                <td>
                    <select id="shortcode_team_id">
                        <option value=""><?php esc_html_e('Select a team', 'stagent'); ?></option>
                        <?php foreach ($teams as $team) : ?>
                            <?php
                            $team_id = sanitize_text_field($team['id'] ?? '');
                            $team_type = sanitize_text_field($team['type'] ?? '');
                            $team_name = sanitize_text_field($team['name'] ?? '');
                            ?>
                            <option value="<?php echo esc_attr($team_id); ?>"
                                    data-type="<?php echo esc_attr($team_type); ?>">
                                <?php echo esc_html($team_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr id="artists_row" style="display: none;">
                <th scope="row"><?php esc_html_e('Artists', 'stagent'); ?></th>
                <td>
                    <select id="shortcode_artists" multiple size="5" style="width: 100%;"></select>
                    <p class="description"><?php esc_html_e('Hold down the Ctrl (Windows) or Command (Mac) button to select multiple artists.', 'stagent'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Booking list options', 'stagent'); ?></th>
                <td>
                    <fieldset>
                        <ul>
                            <li>
                                <label for="shortcode_show"><?php esc_html_e('Bookings to show:', 'stagent'); ?></label>
                                <select id="shortcode_show">
                                    <option value="all"><?php esc_html_e('All (upcoming and past)', 'stagent'); ?></option>
                                    <option value="upcoming"><?php esc_html_e('Upcoming only', 'stagent'); ?></option>
                                    <option value="past"><?php esc_html_e('Past only', 'stagent'); ?></option>
                                </select>
                            </li>
                            <li>
                                <label for="shortcode_per_page"><?php esc_html_e('Bookings per page:', 'stagent'); ?></label>
                                <input type="number" id="shortcode_per_page" min="1" value="5" class="small-text" />
                            </li>
                        </ul>
                        <label for="shortcode_show_past">
                            <input type="checkbox" id="shortcode_show_past" />
                            <?php esc_html_e('Show past bookings', 'stagent'); ?>
                        </label>
                        <br>
                        <label for="shortcode_canceled">
                            <input type="checkbox" id="shortcode_canceled" />
                            <?php esc_html_e('Include canceled bookings', 'stagent'); ?>
                        </label>
                    </fieldset>
                </td>
            </tr>
            </tbody>
        </table>

        <button type="button" class="button button-primary" id="generate_shortcode">
            <?php esc_html_e('Generate shortcode', 'stagent'); ?>
        </button>

        <div id="shortcode_output" style="margin-top: 15px;">
            <input type="text" readonly id="shortcode_result" class="regular-text" value="[stagent_bookings]" />
            <button type="button" class="button copy-button" id="copy_shortcode">
                <?php esc_html_e('Copy', 'stagent'); ?>
            </button>
        </div>
    </div>
</div>