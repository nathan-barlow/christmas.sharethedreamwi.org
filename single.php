
<?php
get_header();
?>

    <div class="wrapper page-header">
        <h1><?php the_title(); ?></h1>
    </div>
</header>
<main>
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <div class="wrapper">
    <?php 
        $event_date = get_post_meta(get_the_id(), 'event-date')[0];
        $event_time = get_post_meta(get_the_id(), 'event-time')[0];

        if($event_date && $event_time) :?>
        <section class="grid grid-3">
            <div class="event-info">
                <div class="event event-date">
                    <i class="bi bi-calendar2-event-fill"></i>
                    <span>
                        <strong><?php echo $event_date ?></strong>
                        <?php echo $event_time ?>
                    </span>
                </div>
                <a class="event-button event event-location" href="https://www.google.com/maps?opi=89978449&client=safari&lqi=Cgl0aGUgZ3JhbmRI7erby-WAgIAIWhMQABABGAAYASIJdGhlIGdyYW5kkgEMYmFucXVldF9oYWxsmgEkQ2hkRFNVaE5NRzluUzBWSlEwRm5TVU5DZW5CaFkzVm5SUkFCqgFAEAEqDSIJdGhlIGdyYW5kKAAyHhABIhrcuoNKlDGAva94-A5o44FwMQjXPyljwYE6NzINEAIiCXRoZSBncmFuZOABAA&phdesc=i3cRW0VnLzA&vet=12ahUKEwiJy9i63J6AAxVGlWoFHTXPB7cQ1YkKegQIFhAB..i&cs=0&um=1&ie=UTF-8&fb=1&gl=us&sa=X&geocode=KckYxtzNtgOIMfF_QCD32MFc&daddr=2621+N+Oneida+St,+Appleton,+WI+54911" target="_blank">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>
                        <strong>The Grand Meridian</strong>
                        2621 N Oneida Street
                    </span>
                </a>
                <div class="message message-error">
                    Please note that this is an invite-only event! Make sure to register before finalizing your plans to attend.
                </div>
            </div>
            <div class="span-2">
                <?php the_content(); ?>
            </div>
        </section>
    <?php else : ?>
        <section>
            <?php the_content(); ?>
        </section>
    <?php endif; ?>

    </div>
    <?php endwhile; else: endif; ?>
</main>

<?php
get_footer();
?>
