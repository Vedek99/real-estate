<?php
get_header(); 

if (have_posts()) : 
    while (have_posts()) : the_post();
?>

<section class="real-estate-single">
    <div class="container">
        <h1><?php the_title(); ?></h1>

        <div class="real-estate-details">
            <div class="about-building">
                <p><strong>Назва будинку:</strong> <?php the_field('building_name'); ?></p>
                <p><strong>Координати місцезнаходження:</strong> <?php the_field('location_coordinates'); ?></p>
                <p><strong>Кількість поверхів:</strong> <?php the_field('number_of_floors'); ?></p>
                <p><strong>Тип будівлі:</strong> <?php the_field('type_of_building'); ?></p>
                <p><strong>Екологічність:</strong> <?php the_field('environmental_friendliness'); ?></p>
            </div>

            <?php if (get_field('image')) : ?>
                <img src="<?php the_field('image'); ?>" alt="Зображення будівлі">
            <?php endif; ?>

            <div class="about-rooms">
                <h3>Приміщення</h3>
                <?php if (have_rows('room_type')) : ?>
                    <ul>
                        <?php while (have_rows('room_type')) : the_row(); ?>
                            <li>
                                <p><strong>Площа:</strong> <?php the_sub_field('area'); ?> м²</p>
                                <p><strong>Кількість кімнат:</strong> <?php the_sub_field('number_of_rooms'); ?></p>
                                <p><strong>Балкон:</strong> <?php the_sub_field('balcony'); ?></p>
                                <p><strong>Санвузол:</strong> <?php the_sub_field('bathroom'); ?></p>
                                <?php if (get_sub_field('room_image')) : ?>
                                    <img src="<?php the_sub_field('room_image'); ?>" alt="Зображення приміщення">
                                <?php endif; ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else : ?>
                    <p>Немає приміщень для відображення.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
    endwhile;
else :
    echo '<p>Пост не знайдено.</p>';
endif;

get_footer(); 
?>