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

    ?>
    <?php get_template_part( 'template-parts/buildings/filter-real_estate' ); ?>
    <?php

    return ob_get_clean(); 
}

add_shortcode('real_estate_filter', 'real_estate_filter_shortcode');

function filter_real_estate() {
    $paged = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $posts_per_page = 5;

    $args = array(
        'post_type' => 'real_estate',
        'posts_per_page' => $posts_per_page,
        'paged' => $paged,
        'meta_query' => array()
    );

    if (isset($_GET['building_name']) && !empty($_GET['building_name'])) {
        $args['meta_query'][] = array(
            'key' => 'building_name',
            'value' => sanitize_text_field($_GET['building_name']),
            'compare' => 'LIKE'
        );
    }

    if (isset($_GET['location_coordinates']) && !empty($_GET['location_coordinates'])) {
        $args['meta_query'][] = array(
            'key' => 'location_coordinates',
            'value' => sanitize_text_field($_GET['location_coordinates']),
            'compare' => 'LIKE'
        );
    }

    if (isset($_GET['number_of_floors']) && !empty($_GET['number_of_floors'])) {
        $args['meta_query'][] = array(
            'key' => 'number_of_floors',
            'value' => intval($_GET['number_of_floors']),
            'compare' => '='
        );
    }

    if (isset($_GET['type_of_building']) && !empty($_GET['type_of_building'])) {
        $args['meta_query'][] = array(
            'key' => 'type_of_building',
            'value' => sanitize_text_field($_GET['type_of_building']),
            'compare' => '='
        );
    }

    if (isset($_GET['environmental_friendliness']) && !empty($_GET['environmental_friendliness'])) {
        $args['meta_query'][] = array(
            'key' => 'environmental_friendliness',
            'value' => intval($_GET['environmental_friendliness']),
            'compare' => '='
        );
    }

    $query = new WP_Query($args);

    ob_start(); 

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post(); ?>
            <a class="real-estate-details" href="<?php the_permalink(); ?>">
                <div class="details-image">
                    <div class="about-building">
                        <h2><?php the_title(); ?></h2>
                        <p><strong><?php esc_html_e('Назва будинку:', 'text-domain'); ?></strong> <?php the_field('building_name'); ?></p>
                        <p><strong><?php esc_html_e('Координати місцезнаходження:', 'text-domain'); ?></strong> <?php the_field('location_coordinates'); ?></p>
                        <p><strong><?php esc_html_e('Кількість поверхів:', 'text-domain'); ?></strong> <?php the_field('number_of_floors'); ?></p>
                        <p><strong><?php esc_html_e('Тип будівлі:', 'text-domain'); ?></strong> <?php the_field('type_of_building'); ?></p>
                        <p><strong><?php esc_html_e('Екологічність:', 'text-domain'); ?></strong> <?php the_field('environmental_friendliness'); ?></p>
                    </div>
                    <?php if (get_field('image')) : ?>
                        <img src="<?php the_field('image'); ?>" alt="Зображення будівлі">
                    <?php endif; ?>
                </div>
            </a>
        <?php endwhile;

        $pagination_args = array(
            'total' => $query->max_num_pages,
            'current' => $paged,
            'format' => '?page=%#%',
            'prev_text' => __('« Previous', 'text-domain'),
            'next_text' => __('Next »', 'text-domain'),
            'before_page_number' => '<a href="#" data-page="%#%">',
            'after_page_number' => '</a>'
        );
        $pagination = paginate_links($pagination_args);
        echo '<div class="pagination">' . $pagination . '</div>';

    else :
        echo '<p>Немає об\'єктів для відображення.</p>';
    endif;

    wp_reset_postdata();

    wp_send_json(array(
        'html' => ob_get_clean(), 
        'pagination' => $pagination 
    ));
}
add_action('wp_ajax_filter_real_estate', 'filter_real_estate');
add_action('wp_ajax_nopriv_filter_real_estate', 'filter_real_estate');

class RealEstateFilterWidget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'real_estate_filter_widget',
            __('Фільтр нерухомості вiджет', 'test-domain'),
            array('description' => __('Відображає фільтр нерухомості.', 'test-domain'))
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];

        ?>
        <form method="GET" class="real-estate-filter-widget">
            <div>
                <label for="building_name_widget"><?php echo esc_html_e('Назва будинку:', 'test-domain') ?></label>
                <input type="text" id="building_name_widget" name="building_name" value="<?php echo isset($_GET['building_name']) ? esc_attr($_GET['building_name']) : ''; ?>">
            </div>
            <div>
                <label for="type_of_building_widget"><?php echo esc_html_e('Тип будівлі:', 'test-domain') ?></label>
                <select id="type_of_building_widget" name="type_of_building">
                    <option value=""><?php echo esc_html_e('Будь-який тип', 'test-domain') ?></option>
                    <option value="панель" <?php echo isset($_GET['type_of_building']) && $_GET['type_of_building'] == 'панель' ? 'selected' : ''; ?>><?php echo esc_html_e('Панель', 'test-domain') ?></option>
                    <option value="цегла" <?php echo isset($_GET['type_of_building']) && $_GET['type_of_building'] == 'цегла' ? 'selected' : ''; ?>><?php echo esc_html_e('Цегла', 'test-domain') ?></option>
                </select>
            </div>
            <input type="submit" value="Фільтрувати">
        </form>
        <?php

        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Фільтр нерухомості вiджет', 'test-domain');
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Заголовок:'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <?php 
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
}

function register_real_estate_filter_widget() {
    register_widget('RealEstateFilterWidget');
}
add_action('widgets_init', 'register_real_estate_filter_widget');

?>

