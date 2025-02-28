<?php
/**
 * Template: stagent-booking-item.php
 *
 * Renders an individual booking item for the Stagent plugin.
 *
 * @package Stagent
 * @since Stagent v0.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Sanitize and validate variables passed from stagent-booking-list.php
$booking = is_array($booking ?? []) ? $booking : [];
$hide_artist = (bool) ($hide_artist ?? false);
$show_flag = (bool) get_option('stagent_show_flag', true);

// Extract booking data with defaults
$artist      = sanitize_text_field($booking['artist'] ?? '');
$event       = sanitize_text_field($booking['event'] ?? '');
$starts_at   = filter_var($booking['starts_at'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
$starts_at_eff = filter_var($booking['effective_starts_at'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
$is_tba      = (bool) ($booking['is_tba'] ?? false);
$date        = sanitize_text_field($booking['date'] ?? '');
$time        = sanitize_text_field($booking['time'] ?? '');
$tickets     = esc_url_raw($booking['tickets'] ?? '');
$website     = esc_url_raw($booking['website'] ?? '');
$venue       = sanitize_text_field($booking['venue'] ?? '');
$city        = sanitize_text_field($booking['city'] ?? '');
$country     = sanitize_text_field($booking['country'] ?? '');
$flag        = sanitize_text_field($booking['flag'] ?? '');
$canceled    = isset($booking['status']) && $booking['status'] === 'cancelled';
?>

<li class="stagent-booking-list-item">
    <div class="stagent-booking-details <?php echo $canceled ? 'stagent-booking-canceled' : ''; ?>">
        <span class="stagent-booking-datetime">
            <span class="stagent-booking-date">
                <?php echo esc_html(wp_date(get_option('date_format', 'F j, Y'), $starts_at_eff)); ?>
            </span>

            <span class="stagent-booking-time">
                <?php if (!$is_tba) : ?>
                    <?php echo esc_html(wp_date(get_option('time_format', 'g:i a'), $starts_at)); ?>
                <?php else : ?>
                    <?php esc_html_e('TBA', 'stagent'); ?>
                <?php endif; ?>
            </span>
        </span>

        <?php if (!$hide_artist && !empty($artist)) : ?>
            <span class="stagent-booking-artist"><?php echo esc_html($artist); ?></span>
        <?php endif; ?>

        <?php if (!empty($event)) : ?>
            <span class="stagent-booking-event"><?php echo esc_html($event); ?></span>
        <?php endif; ?>

        <?php if (!empty($city)) : ?>
            <span class="stagent-booking-location">
                <span class="stagent-booking-address">
                    <?php echo esc_html($city . ', ' . $country); ?>
                    <?php if ($show_flag && !empty($flag)) : ?>
                        <?php echo ' ' . esc_html($flag); ?>
                    <?php endif; ?>
                </span>

                <?php if (!empty($venue)) : ?>
                    <span class="stagent-booking-venue">// <?php echo esc_html($venue); ?></span>
                <?php endif; ?>
            </span>
        <?php endif; ?>
    </div>

    <div class="stagent-booking-meta">
        <?php if ($canceled) : ?>
            <span class="stagent-booking-canceled-badge"><?php esc_html_e('[canceled]', 'stagent'); ?></span>
        <?php else : ?>
            <?php if (!empty($tickets)) : ?>
                <a href="<?php echo esc_url($tickets); ?>" class="button stagent-booking-tickets" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e('Tickets', 'stagent'); ?>
                </a>
            <?php endif; ?>

            <?php if (!empty($website)) : ?>
                <a href="<?php echo esc_url($website); ?>" class="button stagent-booking-website" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e('Website', 'stagent'); ?>
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</li>