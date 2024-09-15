<?php
/* Template Name: Home */

get_header(); 
?>

<main>
    <?php echo do_shortcode('[real_estate_filter]') ?>
    <?php if ( is_active_sidebar( 'left-sidebar' ) ) : ?>
    <aside id="secondary" class="widget-area">
        <?php dynamic_sidebar( 'left-sidebar' ); ?>
    </aside>
    <?php endif; ?>
</main>

<?php get_footer(); ?>