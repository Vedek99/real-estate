<section class="real-estate">
    <h1><?php echo esc_html_e('Об`єкти нерухомості', 'test-domain') ?></h1>
    
    <form method="GET" class="real-estate-filter">
        <div class="real-estate-filter-params">
            <div>
                <label for="building_name"><?php echo esc_html_e('Назва будинку:', 'test-domain') ?></label>
                <input type="text" id="building_name" name="building_name" value="<?php echo isset($_GET['building_name']) ? esc_attr($_GET['building_name']) : ''; ?>">
            </div>
            
            <div>
                <label for="location_coordinates"><?php echo esc_html_e('Координати місцезнаходження:', 'test-domain') ?></label>
                <input type="text" id="location_coordinates" name="location_coordinates" value="<?php echo isset($_GET['location_coordinates']) ? esc_attr($_GET['location_coordinates']) : ''; ?>">
            </div>
            
            <div>
                <label for="number_of_floors"><?php echo esc_html_e('Кількість поверхів:', 'test-domain') ?></label>
                <select id="number_of_floors" name="number_of_floors">
                    <option value=""><?php echo esc_html_e('Будь-яка кількість', 'test-domain') ?></option>
                    <?php for ($i = 1; $i <= 20; $i++) : ?>
                        <option value="<?php echo $i; ?>" <?php echo isset($_GET['number_of_floors']) && $_GET['number_of_floors'] == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div>
                <label for="type_of_building"><?php echo esc_html_e('Тип будівлі:', 'test-domain') ?></label>
                <select id="type_of_building" name="type_of_building">
                    <option value=""><?php echo esc_html_e('Будь-який тип', 'test-domain') ?></option>
                    <option value="панель" <?php echo isset($_GET['type_of_building']) && $_GET['type_of_building'] == 'панель' ? 'selected' : ''; ?>><?php echo esc_html_e('Панель', 'test-domain') ?></option>
                    <option value="цегла" <?php echo isset($_GET['type_of_building']) && $_GET['type_of_building'] == 'цегла' ? 'selected' : ''; ?>><?php echo esc_html_e('Цегла', 'test-domain') ?></option>
                    <option value="піноблок" <?php echo isset($_GET['type_of_building']) && $_GET['type_of_building'] == 'піноблок' ? 'selected' : ''; ?>><?php echo esc_html_e('Піноблок', 'test-domain') ?></option>
                </select>
            </div>

            <div>
                <label for="environmental_friendliness"><?php echo esc_html_e('Екологічність:', 'test-domain') ?></label>
                <select id="environmental_friendliness" name="environmental_friendliness">
                    <option value=""><?php echo esc_html_e('Будь-яка', 'test-domain') ?></option>
                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                        <option value="<?php echo $i; ?>" <?php echo isset($_GET['environmental_friendliness']) && $_GET['environmental_friendliness'] == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>

        <div class="filter-button">
            <input type="submit" value="Фільтрувати">
        </div>
    </form>

    <div class="real-estate__container container">
        <?php
        $args = array(
            'post_type' => 'real_estate',
            'posts_per_page' => -1,
            'meta_query' => array()
        );

        if (isset($_GET['building_name']) && !empty($_GET['building_name'])) {
            $args['meta_query'][] = array(
                'key' => 'building_name',
                'value' => esc_attr($_GET['building_name']),
                'compare' => 'LIKE'
            );
        }

        if (isset($_GET['location_coordinates']) && !empty($_GET['location_coordinates'])) {
            $args['meta_query'][] = array(
                'key' => 'location_coordinates',
                'value' => esc_attr($_GET['location_coordinates']),
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
                'value' => esc_attr($_GET['type_of_building']),
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

        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
        ?>
            <a class="real-estate-details" href="<?php echo get_permalink() ?>">
                <div class="details-image">
                    <div class="about-building">
                        <h2><?php the_title(); ?></h2>
                        <p><strong><?php echo esc_html_e('Назва будинку:', 'test-domain') ?></strong> <?php the_field('building_name'); ?></p>
                        <p><strong><?php echo esc_html_e('Координати місцезнаходження:', 'test-domain') ?></strong> <?php the_field('location_coordinates'); ?></p>
                        <p><strong><?php echo esc_html_e('Кількість поверхів:', 'test-domain') ?></strong> <?php the_field('number_of_floors'); ?></p>
                        <p><strong><?php echo esc_html_e('Тип будівлі:', 'test-domain') ?></strong> <?php the_field('type_of_building'); ?></p>
                        <p><strong><?php echo esc_html_e('Екологічність:', 'test-domain') ?></strong> <?php the_field('environmental_friendliness'); ?></p>
                    </div>

                    <?php if (get_field('image')) : ?>
                        <img src="<?php the_field('image'); ?>" alt="Зображення будівлі">
                    <?php endif; ?>
                </div>

                <div class="about-rooms">
                    <h3><?php echo esc_html_e('Приміщення', 'test-domain') ?></h3>
                    <?php if (have_rows('room_type')) : ?>
                        <ul>
                            <?php while (have_rows('room_type')) : the_row(); ?>
                                <li>
                                    <p><strong><?php echo esc_html_e('Площа:', 'test-domain') ?></strong> <?php the_sub_field('area'); ?> м²</p>
                                    <p><strong><?php echo esc_html_e('Кількість кімнат:', 'test-domain') ?></strong> <?php the_sub_field('number_of_rooms'); ?></p>
                                    <p><strong><?php echo esc_html_e('Балкон:', 'test-domain') ?></strong> <?php the_sub_field('balcony'); ?></p>
                                    <p><strong><?php echo esc_html_e('Санвузол:', 'test-domain') ?></strong> <?php the_sub_field('bathroom'); ?></p>
                                    <?php if (get_sub_field('room_image')) : ?>
                                        <img src="<?php the_sub_field('room_image'); ?>" alt="Зображення приміщення">
                                    <?php endif; ?>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else : ?>
                        <p><?php echo esc_html_e('Немає приміщень для відображення.', 'test-domain') ?></p>
                    <?php endif; ?>
                </div>
                
            </a>

        <?php
            endwhile;
            wp_reset_postdata();
        else :
            echo '<p>Немає об\'єктів для відображення.</p>';
        endif;
        ?>
    </div>
</section>