<?php
/**
 * Template: stagent-booking-container.php
 *
 * Renders the container for upcoming and past bookings with toggle and load more functionality.
 *
 * @package Stagent
 * @since Stagent v0.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Sanitize and validate variables passed from Stagent_Shortcodes::display_bookings
$show = in_array($show ?? 'all', ['all', 'past', 'upcoming'], true) ? $show : 'all';
$team = sanitize_text_field($team ?? '');
$artists = array_map('sanitize_text_field', (array) ($artists ?? []));
$hide_artist = (bool) ($hide_artist ?? false);
$per_page = max(1, filter_var($per_page ?? 5, FILTER_VALIDATE_INT, ['options' => ['default' => 5]]));
$upcoming = is_array($upcoming ?? []) ? $upcoming : [];
$past = is_array($past ?? []) ? $past : [];
$show_past_enabled = (bool) ($show_past ?? true);
$dark_mode_enabled = (bool) ($dark_mode ?? false);
$show_powered_by = (bool) get_option('stagent_powered_by', false);
?>

<div class="stagent-bookings-container <?php echo $dark_mode_enabled ? 'dark' : ''; ?>">
    <?php if ($show_past_enabled && $show === 'all') : ?>
        <div class="stagent-bookings-toggle">
            <a href="javascript:void(0);" class="button stagent-toggle-past"><?php esc_html_e('Past', 'stagent'); ?></a>
            <a href="javascript:void(0);" class="button stagent-toggle-upcoming active"><?php esc_html_e('Upcoming', 'stagent'); ?></a>
        </div>
    <?php endif; ?>

    <?php if ($show !== 'past') : ?>
        <?php
        $upcoming_list = Stagent_Template::render('stagent-bookings-list.php', [
            'data'        => isset($upcoming['data']) && is_array($upcoming['data']) ? $upcoming['data'] : [],
            'hide_artist' => $hide_artist,
            'list_class'  => 'stagent-bookings-upcoming',
            'visible'     => ($show === 'all' || $show === 'upcoming'),
        ], true);
        echo wp_kses_post($upcoming_list);
        ?>
    <?php endif; ?>

    <?php if ($show !== 'upcoming' && !empty($past['data']) && is_array($past['data'])) : ?>
        <?php
        $past_list = Stagent_Template::render('stagent-bookings-list.php', [
            'data'        => $past['data'],
            'hide_artist' => $hide_artist,
            'list_class'  => 'stagent-bookings-past',
            'visible'     => $show === 'past',
        ], true);
        echo wp_kses_post($past_list);
        ?>
    <?php endif; ?>

    <div class="stagent-bookings-footer">
        <div class="stagent-bookings-footer-start">
            <a href="javascript:void(0);" class="button stagent-load-more"
               data-upcoming-page="1"
               data-past-page="1"
               data-team="<?php echo esc_attr($team); ?>"
               data-artists="<?php echo esc_attr(implode(',', $artists)); ?>"
               data-per-page="<?php echo esc_attr($per_page); ?>">
                <?php esc_html_e('Load more', 'stagent'); ?>
            </a>
        </div>

        <div class="stagent-bookings-footer-end">
            <?php if ($show_powered_by) : ?>
                <a href="https://stagent.com" class="button stagent-powered-by" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e('Powered by Stagent', 'stagent'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>