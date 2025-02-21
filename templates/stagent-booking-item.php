<?php
/**
 * Template: stagent-booking-item.php
 */

// Fetched booking data
$artist    = esc_html($booking['artist']);
$event     = esc_html(isset($booking['event']) ? $booking['event'] : 'No event name');
$time      = esc_html(isset($booking['time']) ? $booking['time'] : null);
$tickets   = esc_html(isset($booking['tickets']) ? $booking['tickets'] : null);
$website   = esc_html(isset($booking['website']) ? $booking['website'] : null);
$venue     = esc_html(isset($booking['venue']) ? $booking['venue'] : 'No venue');
$city      = esc_html(isset($booking['city']) ? $booking['city'] : 'No city');
$country   = esc_html(isset($booking['country']) ? $booking['country'] : 'No country');
$flag      = isset($booking['flag']) ? esc_html($booking['flag']) : null;
$show_flag = (bool) get_option('stagent_show_flag');

if (!isset($hide_artist)) {
    $hide_artist = false;
}

$canceled = ($booking['status'] === 'cancelled');
?>

<li class="stagent-booking-item">
    <div class="stagent-booking-details <?php echo $canceled ? 'stagent-booking-canceled' : ''; ?>">
        <span class="stagent-booking-date">
            <?php echo $booking['date']; ?>

            <span class="stagent-booking-time">
                <?php echo $time; ?>
            </span>
        </span>

        <?php if (!$hide_artist) : ?>
            <span class="stagent-booking-artist">
                <?php echo $artist; ?>
            </span>
        <?php endif; ?>

        <span class="stagent-booking-event">
            <?php echo $booking['event']; ?>
        </span>

        <?php if (!empty($city)) : ?>
            <span class="stagent-booking-location">
                <?php echo $city . ', ' . $country; ?>

                <?php if ($show_flag && $flag) : ?>
                    <?php echo ' ' . $flag; ?>
                <?php endif; ?>

                <?php if (!empty($venue)) : ?>
                    <span class="stagent-booking-venue">
                        //
                        <?php echo $venue; ?>
                    </span>
                <?php endif; ?>
            </span>
        <?php endif; ?>
    </div>

    <div class="stagent-booking-meta">
        <?php if ($canceled) : ?>
            <span class="stagent-booking-canceled-badge">[canceled]</span>
        <?php else : ?>
            <?php if ($tickets = $booking['tickets']) : ?>
                <a href="<?php echo $tickets; ?>"
                   class="button stagent-booking-tickets" target="_blank">
                    Tickets
                </a>
            <?php endif; ?>

            <?php if ($website = $booking['website']) : ?>
                <a href="<?php echo $website; ?>"
                   class="button stagent-booking-website" target="_blank">
                    Website
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</li>