<?php
/**
 * Template: stagent-booking-list.php
 *
 * Renders a list of bookings (upcoming or past) for the Stagent plugin.
 *
 * @package Stagent
 * @since Stagent v0.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Sanitize and validate variables passed from stagent-booking-container.php
$list_class = sanitize_html_class($list_class ?? '');
$visible = (bool) ($visible ?? false);
$data = is_array($data ?? []) ? $data : [];
$hide_artist = (bool) ($hide_artist ?? false);

$classes = $list_class . ($visible ? '' : ' hidden');
?>

<ul class="stagent-bookings-list <?php echo esc_attr($classes); ?>">
    <?php if (empty($data)) : ?>
        <li><?php esc_html_e('No bookings found.', 'stagent'); ?></li>
    <?php else : ?>
        <?php foreach ($data as $booking) : ?>
            <?php
            $booking = is_array($booking) ? $booking : [];
            $booking_item = Stagent_Template::render('stagent-booking-list-item.php', [
                'booking'     => $booking,
                'hide_artist' => $hide_artist,
            ], true);
            echo wp_kses_post($booking_item);
            ?>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>