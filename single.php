
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
                        <span id="event-time"><?php echo $event_time ?></span>
                    </span>
                </div>
                <a class="event-button event event-location" href="https://www.google.com/maps/place/Holy+Spirit+Catholic+School/@44.2440713,-88.3302202,181m/data=!3m1!1e3!4m6!3m5!1s0x8803baa93be7c6bb:0xbebcb01ac38f0be1!8m2!3d44.2440876!4d-88.3297398!16s%2Fg%2F1td5gps0?entry=ttu" target="_blank">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>
                        <strong>Holy Spirit Catholic School</strong>
                        W2796 County Rd KK
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

<script>
    const event_time_element = document.getElementById("event-time");
    const event_time = event_time_element.innerText;
    const user_event_time = localStorage.getItem('std2023_event_time');
    if(event_time_element && user_event_time) {
        event_time_element.innerHTML = user_event_time + `<button data-text="This is your reserved time stored locally on your device from your registration. Please arrive anytime within this window. Entire event will be held from ${event_time}." class="button-mini button-icon-only tooltip button-gray-100"><i class="bi bi-info-circle"></i></button>`;
    }
</script>

<?php
get_footer();
?>
