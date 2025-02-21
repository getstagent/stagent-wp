<?php
/**
 * Template: stagent-settings-page.php
 */
?>

<div class="wrap">
    <h2>Stagent Settings</h2>
    <form method="post" action="options.php">
        <?php settings_fields('stagent_settings_group'); ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">Personal access token</th>
                    <td>
                        <fieldset>
                            <input type="text" name="stagent_api_key"
                                   value="<?php echo esc_attr($api_key); ?>"
                                   class="regular-text ltr">

                            <?php if (empty($teams)) : ?>
                                <?php if ($api_key) : ?>
                                    <p class="description">Not a valid personal access token.</p>
                                <?php else : ?>
                                    <p class="description">
                                        Generate your personal access token
                                        <a href="https://stagent.com/app/account/api-tokens" target="_blank">here</a>.
                                    </p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Default team</th>
                    <td>
                        <fieldset>
                            <select name="stagent_default_team_id" <?php echo empty($teams) ? 'disabled' : '' ?>>
                                <option value="">Select a team</option>
                                <?php foreach ($teams as $team) : ?>
                                    <option value="<?php echo esc_attr($team['id']); ?>"
                                        <?php selected($default_team_id, $team['id']); ?>>
                                        <?php echo esc_html($team['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <?php if ($api_key && !empty($teams)) : ?>
                                <a href="<?php echo add_query_arg('stagent_refresh_teams', '1'); ?>" class="button">
                                    Refresh
                                </a>
                            <?php endif; ?>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Booking list options</th>
                    <td>
                        <fieldset>
                            <label for="stagent_show_past">
                                <input type="checkbox" name="stagent_show_past" value="1"
                                       id="stagent_show_past"
                                    <?php checked($show_past, 1); ?>
                                />
                                Show past bookings
                            </label>
                            <br>
                            <label for="stagent_show_canceled">
                                <input type="checkbox" name="stagent_show_canceled" value="1"
                                       id="stagent_show_canceled"
                                    <?php checked($show_canceled, 1); ?>
                                />
                                Show canceled bookings
                            </label>
                            <br>
                            <label for="stagent_show_flag">
                                <input type="checkbox" name="stagent_show_flag" value="1"
                                       id="stagent_show_flag"
                                    <?php checked($show_flag, 1); ?>
                                />
                                Show country flags
                            </label>
                            <br>
                            <label for="stagent_dark_mode">
                                <input type="checkbox" name="stagent_dark_mode" value="1"
                                       id="stagent_dark_mode"
                                    <?php checked($dark_mode, 1); ?>
                                />
                                Enable dark mode
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Booking widget</th>
                    <td>
                        <fieldset>
                            <input type="text" name="stagent_booking_widget"
                                   value="<?php echo esc_attr($booking_widget); ?>"
                                   class="regular-text" id="stagent_booking_widget" />
                            <p class="description">
                                Paste the booking widget &lt;script&gt; tag here.
                                <a href="https://help.stagent.com/article/9-using-the-booking-request-widget"
                                   target="_blank">
                                    Learn more</a>.
                            </p>
                            <br>
                            <label for="stagent_enable_booking_widget">
                                <input type="checkbox" name="stagent_enable_booking_widget"
                                       id="stagent_enable_booking_widget" value="1" />
                                Enable the booking widget on your website
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php submit_button(); ?>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var widgetInput = document.getElementById('stagent_booking_widget');
        var enableCheckbox = document.getElementById('stagent_enable_booking_widget');

        function toggleBookingWidgetCheckbox() {
            if (widgetInput.value.trim() === '') {
                enableCheckbox.disabled = true;
            } else {
                enableCheckbox.disabled = false;
            }
        }

        toggleBookingWidgetCheckbox();

        widgetInput.addEventListener('input', toggleBookingWidgetCheckbox);
    });
</script>