<?php
/**
 * Template: stagent-booking-container.php
 */
?>

<div class="stagent-bookings-container <?php echo get_option('stagent_dark_mode') ? 'dark' : ''; ?>">
    <?php if (get_option('stagent_show_past') && $show === 'all') : ?>
        <div class="stagent-bookings-toggle">
            <a href="javascript:void(0);" class="button stagent-toggle-past">Past</a>
            <a href="javascript:void(0);" class="button stagent-toggle-upcoming active">Upcoming</a>
        </div>
    <?php endif; ?>

    <?php if ($show !== 'past') : ?>
        <?php
            echo Stagent_Template::render('stagent-bookings-list.php', [
                'data'        => isset($upcoming['data']) ? $upcoming['data'] : [],
                'hide_artist' => $hide_artist,
                'list_class'  => 'stagent-bookings-upcoming',
                'visible'     => ($show === 'all' || $show === 'upcoming'),
            ], true);
        ?>
    <?php endif; ?>

    <?php if ($show !== 'upcoming' && !empty($past['data'])) : ?>
        <?php
            echo Stagent_Template::render('stagent-bookings-list.php', [
                'data'        => isset($past['data']) ? $past['data'] : [],
                'hide_artist' => $hide_artist,
                'list_class'  => 'stagent-bookings-past',
                'visible'     => $show === 'past',
            ], true);
        ?>
    <?php endif; ?>

    <div class="stagent-bookings-footer">
        <a href="javascript:void(0);" class="button stagent-load-more"
           data-upcoming-page="1"
           data-past-page="1"
           data-team="<?php echo esc_attr($team); ?>"
           data-artists="<?php echo esc_attr(implode(',', $artists)); ?>"
           data-per-page="<?php echo esc_attr($per_page); ?>">
            Load more
        </a>

        <a href="https://stagent.com" class="button stagent-powered-by" target="_blank">
            Powered by Stagent
        </a>
    </div>
</div>