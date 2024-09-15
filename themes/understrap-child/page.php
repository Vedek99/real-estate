<?php get_header(); ?>

<div class="container">
<?php if ( is_active_sidebar( 'left-sidebar' ) ) : ?>
    <aside id="secondary" class="widget-area">
        <?php dynamic_sidebar( 'left-sidebar' ); ?>
    </aside>
<?php endif; ?>
    <h1><?php the_title(); ?></h1>
    
    <div class="content">
        <?php
        if (have_posts()) :
            while (have_posts()) : the_post();
                the_content();
            endwhile;
        endif;
        ?>
    </div>
</div>

<?php get_footer(); ?>