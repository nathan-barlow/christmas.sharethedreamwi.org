<?php
/* Template Name: Registration CLOSED */
get_header('archive');
?>
</header>
<main>
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    
    <div class="wrapper wrapper-narrow">
        <h1>Registration is closed</h1>
        <section>
            <p>
                We are not currently accepting new reservations for this year's event. Please <a href="#footer-contact">contact us</a> if you think this is a mistake.
            </p>
        </section>
    <?php endwhile; else: endif; ?>
    <?php $recentPost = new WP_Query(array(
            'category_name' => 'events',
            'post_status' => 'publish',
            'posts_per_page' => 1,
        )); ?>
    <?php if ( $recentPost->have_posts() ) : while ( $recentPost->have_posts() ) : $recentPost->the_post(); ?>
        <a class="button" href="<? the_permalink() ?>">View Event Info</a>
    <?php endwhile; endif; ?>

    </div>
</main>

<?php
get_footer();
?>
