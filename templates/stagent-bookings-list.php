<?php
/**
 * Template: stagent-booking-list.php
 */

$classes = $list_class . ' ' . ( $visible ? '' : 'hidden' );
?>

<ul class="stagent-bookings-list <?php echo esc_attr($classes); ?>">
    <?php if (empty($data)) : ?>
        <li>No bookings found.</li>
    <?php else : ?>
        <?php foreach ($data as $booking) : ?>
            <?php
            echo Stagent_Template::render('stagent-booking-item.php', [
                'booking' => $booking,
                'hide_artist'=> $hide_artist,
            ], true);
            ?>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>