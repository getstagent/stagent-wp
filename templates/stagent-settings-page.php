<?php
/**
 * Template: stagent-settings-page.php
 *
 * Renders the Stagent plugin settings page in the WordPress admin.
 *
 * @package Stagent
 * @since Stagent v0.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Sanitize and validate variables passed from Stagent_Settings::render_settings_tab
$api_key = sanitize_text_field($api_key ?? '');
$default_team_id = sanitize_text_field($default_team_id ?? '');
$show_past = (bool) ($show_past ?? true);
$show_canceled = (bool) ($show_canceled ?? false);
$show_flag = (bool) ($show_flag ?? true);
$dark_mode = (bool) ($dark_mode ?? false);
$booking_widget = $booking_widget ?? '';
$enable_booking_widget = (bool) ($enable_booking_widget ?? false);
$teams = is_array($teams ?? []) ? $teams : [];
?>

<div class="wrap">
    <h2><?php esc_html_e('Stagent Settings', 'stagent'); ?></h2>
    <form method="post" action="options.php">
        <?php settings_fields('stagent_settings_group'); ?>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><?php esc_html_e('Personal access token', 'stagent'); ?></th>
                <td>
                    <fieldset>
                        <input type="text" name="stagent_api_key"
                               value="<?php echo esc_attr($api_key); ?>"
                               class="regular-text ltr">

                        <?php if (empty($teams)) : ?>
                            <?php if ($api_key) : ?>
                                <p class="description"><?php esc_html_e('Not a valid personal access token.', 'stagent'); ?></p>
                            <?php else : ?>
                                <p class="description">
                                    <?php
                                    printf(
                                    /* translators: %s: URL to generate API token */
                                        esc_html__('Generate your personal access token %s.', 'stagent'),
                                        '<a href="https://stagent.com/app/account/api-tokens" target="_blank" rel="noopener noreferrer">' . esc_html__('here', 'stagent') . '</a>'
                                    );
                                    ?>
                                </p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Default team', 'stagent'); ?></th>
                <td>
                    <fieldset>
                        <select name="stagent_default_team_id" <?php echo empty($teams) ? 'disabled' : ''; ?> class="regular-text">
                            <option value=""><?php esc_html_e('Select a team', 'stagent'); ?></option>
                            <?php foreach ($teams as $team) : ?>
                                <?php
                                $team_id = sanitize_text_field($team['id'] ?? '');
                                $team_name = sanitize_text_field($team['name'] ?? '');
                                ?>
                                <option value="<?php echo esc_attr($team_id); ?>"
                                    <?php selected($default_team_id, $team_id); ?>>
                                    <?php echo esc_html($team_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <?php if ($api_key && !empty($teams)) : ?>
                            <a href="<?php echo esc_url(add_query_arg(['stagent_refresh_teams' => '1', 'nonce' => wp_create_nonce('stagent_refresh_teams')])); ?>" class="button">
                                <?php esc_html_e('Refresh', 'stagent'); ?>
                            </a>
                        <?php endif; ?>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Booking list options', 'stagent'); ?></th>
                <td>
                    <fieldset>
                        <label for="stagent_show_past">
                            <input type="checkbox" name="stagent_show_past" value="1"
                                   id="stagent_show_past"
                                <?php checked($show_past, 1); ?> />
                            <?php esc_html_e('Show past bookings', 'stagent'); ?>
                        </label>
                        <br>
                        <label for="stagent_show_canceled">
                            <input type="checkbox" name="stagent_show_canceled" value="1"
                                   id="stagent_show_canceled"
                                <?php checked($show_canceled, 1); ?> />
                            <?php esc_html_e('Include canceled bookings', 'stagent'); ?>
                        </label>
                        <br>
                        <label for="stagent_show_flag">
                            <input type="checkbox" name="stagent_show_flag" value="1"
                                   id="stagent_show_flag"
                                <?php checked($show_flag, 1); ?> />
                            <?php esc_html_e('Show country flags', 'stagent'); ?>
                        </label>
                        <br>
                        <label for="stagent_dark_mode">
                            <input type="checkbox" name="stagent_dark_mode" value="1"
                                   id="stagent_dark_mode"
                                <?php checked($dark_mode, 1); ?> />
                            <?php esc_html_e('Enable dark mode', 'stagent'); ?>
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Booking request widget', 'stagent'); ?></th>
                <td>
                    <fieldset>
                        <label for="stagent_booking_widget">
                            <input type="text" name="stagent_booking_widget"
                                   value="<?php echo esc_attr($booking_widget); ?>"
                                   class="regular-text" id="stagent_booking_widget" />
                        </label>
                        <p class="description">
                            <?php
                            printf(
                            /* translators: %s: URL to learn more about the widget */
                                esc_html__('Paste the widget <script> tag here. %s.', 'stagent'),
                                '<a href="https://help.stagent.com/article/9-using-the-booking-request-widget" target="_blank" rel="noopener noreferrer">' . esc_html__('Learn more', 'stagent') . '</a>'
                            );
                            ?>
                        </p>
                        <br>
                        <label for="stagent_enable_booking_widget">
                            <input type="checkbox" name="stagent_enable_booking_widget"
                                   id="stagent_enable_booking_widget" value="1"
                                <?php checked($enable_booking_widget, 1); ?> />
                            <?php esc_html_e('Enable the booking widget on your website', 'stagent'); ?>
                        </label>
                    </fieldset>
                </td>
            </tr>
            </tbody>
        </table>
        <?php submit_button(); ?>
    </form>
</div>