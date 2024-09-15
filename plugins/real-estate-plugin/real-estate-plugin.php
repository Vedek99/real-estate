<?php
/*
Plugin Name: Real Estate Plugin
Plugin URI:  http://example.com
Description: Плагін для реєстрації "Об'єктів нерухомості" і таксономії "Район".
Version:     1.0
Author:      Ваше ім'я
Author URI:  http://example.com
License:     GPL2
*/

function real_estate_post_type() {
    $labels = array(
        'name'                  => 'Об\'єкти нерухомості',
        'singular_name'         => 'Об\'єкт нерухомості',
        'menu_name'             => 'Нерухомість',
        'name_admin_bar'        => 'Об\'єкт нерухомості',
        'add_new'               => 'Додати новий',
        'add_new_item'          => 'Додати новий об\'єкт нерухомості',
        'new_item'              => 'Новий об\'єкт нерухомості',
        'edit_item'             => 'Редагувати об\'єкт нерухомості',
        'view_item'             => 'Переглянути об\'єкт нерухомості',
        'all_items'             => 'Всі об\'єкти',
        'search_items'          => 'Шукати об\'єкти',
        'not_found'             => 'Об\'єктів не знайдено',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'rewrite'            => array('slug' => 'objects'),
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'capability_type'    => 'post',
        'show_in_rest'       => true,
    );

    register_post_type('real_estate', $args);
}
add_action('init', 'real_estate_post_type');

function real_estate_taxonomy() {
    $labels = array(
        'name'              => 'Райони',
        'singular_name'     => 'Район',
        'search_items'      => 'Шукати райони',
        'all_items'         => 'Всі райони',
        'edit_item'         => 'Редагувати район',
        'update_item'       => 'Оновити район',
        'add_new_item'      => 'Додати новий район',
        'new_item_name'     => 'Назва нового району',
        'menu_name'         => 'Райони',
    );

    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true,
        'show_in_rest'      => true,
        'rewrite'           => array('slug' => 'districts'),
    );

    register_taxonomy('district', array('real_estate'), $args);
}
add_action('init', 'real_estate_taxonomy');

function real_estate_duplicate_link($actions, $post) {
    if ($post->post_type === 'real_estate') {
        $actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=duplicate_real_estate&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce') . '" title="Дублировать этот объект" rel="permalink">Дублировать</a>';
    }
    return $actions;
}
add_filter('post_row_actions', 'real_estate_duplicate_link', 10, 2);

function real_estate_duplicate_post() {
    if (!(isset($_GET['post']) || isset($_POST['post']) || (isset($_REQUEST['action']) && 'duplicate_real_estate' == $_REQUEST['action']))) {
        wp_die('Нет записи для дублирования');
    }

    if (!isset($_GET['duplicate_nonce']) || !wp_verify_nonce($_GET['duplicate_nonce'], basename(__FILE__))) {
        return;
    }

    $post_id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
    $post = get_post($post_id);

    if (isset($post) && $post != null) {
        $new_post = array(
            'post_title'    => $post->post_title . ' (Копія)',
            'post_content'  => $post->post_content,
            'post_status'   => 'draft',
            'post_type'     => $post->post_type,
            'post_author'   => $post->post_author,
            'post_excerpt'  => $post->post_excerpt,
            'post_category' => wp_get_post_categories($post_id)
        );

        $new_post_id = wp_insert_post($new_post);

        $meta_fields = get_post_meta($post_id);
        foreach ($meta_fields as $key => $value) {
            if ($key !== '_wp_old_slug') {
                update_post_meta($new_post_id, $key, maybe_unserialize($value[0]));
            }
        }

        $taxonomies = get_object_taxonomies($post->post_type);
        foreach ($taxonomies as $taxonomy) {
            $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
            wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
        }

        wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
        exit;
    } else {
        wp_die('Ошибка: невозможно дублировать запись');
    }
}
add_action('admin_action_duplicate_real_estate', 'real_estate_duplicate_post');

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

        echo do_shortcode('[real_estate_filter]');

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