
<?php
get_header( 'archive' );
?>

    <div class="wrapper page-header">
        <h1>
            <?php
            if( is_day() ) _e( 'You are viewing posts from ' .  get_the_date());
            elseif ( is_month() ) _e( 'You are viewing archives for the month of ' . get_the_date( 'F Y' ));
            elseif ( is_year() ) _e( 'You are viewing the ' . get_the_date( 'Y' ) . ' yearly archives' );
            elseif ( is_author() ) _e( 'You are viewing ' . get_the_author() . '\'s archives' );
            else _e( single_cat_title( '', false ) );
            ?>
        </h1>
    </div>
</header>
<main>
    <section>
        <div class="wrapper">
            <ol id="content" class="grid grid-2 cards">
                <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                <li class="floating-card">
                    <a class="card-link" href="<?php the_permalink(); ?>" title="Read More About:  <?php the_title_attribute(); ?>">
                        <figure class="card-figure">
                            <?php the_post_thumbnail('card-small'); ?>
                        </figure>
                        <div class="card-text">
                            <h2 class="card-title">
                                <?php the_title(); ?>
                            </h2>
                            <p>
                                <?php the_excerpt(); ?>
                            </p>
                        </div>
                    </a>
                </li>
                <? endwhile; ?>
            </ol>
            <?php
            the_posts_pagination( array(
                'mid_size' => 2,
                'prev_text' => __( '<i class="bi bi-arrow-left"></i>', 'textdomain' ),
                'next_text' => __( '<i class="bi bi-arrow-right"></i>', 'textdomain' ),
            ) );
            else: ?>
            <section style="grid-column: 1 / 4" class="card-section">
                <p class="card-section-excerpt"><?php _e( 'Sorry, no posts yet!' ); ?></p>
            </section>
            
            <?php endif; ?>
        </div>
    </section>
</main>

<?php
get_footer();
?>
