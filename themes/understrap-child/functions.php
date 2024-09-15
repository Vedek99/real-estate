<?php
function my_child_theme_enqueue_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'));
    wp_enqueue_style('child-style-main', get_stylesheet_directory_uri() . '/assets/css/main.css', array('parent-style'));

    wp_enqueue_script('real-estate-ajax', get_stylesheet_directory_uri() . '/assets/js/main.js', array('jquery'), null, true);
    wp_localize_script('real-estate-ajax', 'realEstateAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'my_child_theme_enqueue_styles');

function real_estate_filter_shortcode() {
    ob_start(); 

    get_template_part( 'template-parts/buildings/filter-real_estate' ); 

    return ob_get_clean(); 
}
add_shortcode('real_estate_filter', 'real_estate_filter_shortcode');
?>

