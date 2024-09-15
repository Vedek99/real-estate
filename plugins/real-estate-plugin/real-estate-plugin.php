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