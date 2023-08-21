
<?php
get_header( 'home' );
?>

    <div class="wrapper home-header">
        <h1><?php bloginfo( 'description' ); ?></h1>
        <div class="buttons">
            <a class="button" href="/help-out">See how you can help</a>
            <a class="button button-white" href="/resources">Resources for families in need</a>
        </div>

        <div class="snowflakes">
            <img data-top data-spin="8" id="flake1" class="snowflake" src="/wp-content/uploads/2023/06/snowflake.png" alt="snowflake">
            <img data-top data-spin="-12" id="flake2" class="snowflake" src="/wp-content/uploads/2023/06/snowflake.png" alt="snowflake">
            <img data-top data-spin="9" id="flake3" class="snowflake" src="/wp-content/uploads/2023/06/snowflake.png" alt="snowflake">
            <img data-top data-spin="-5" id="flake4" class="snowflake" src="/wp-content/uploads/2023/06/snowflake.png" alt="snowflake">
        </div>
    </div>
    <div class="waves" style="overflow: hidden;">
        <svg
            preserveAspectRatio="none"
            viewBox="0 0 1200 120"
            xmlns="http://www.w3.org/2000/svg"
            style="fill: #ffffff; width: 131%; height: 72px; transform: rotate(180deg);"
        >
            <path
            d="M0 0v46.29c47.79 22.2 103.59 32.17 158 28 70.36-5.37 136.33-33.31 206.8-37.5 73.84-4.36 147.54 16.88 218.2 35.26 69.27 18 138.3 24.88 209.4 13.08 36.15-6 69.85-17.84 104.45-29.34C989.49 25 1113-14.29 1200 52.47V0z"
            opacity=".25"
        />
            <path
            d="M0 0v15.81c13 21.11 27.64 41.05 47.69 56.24C99.41 111.27 165 111 224.58 91.58c31.15-10.15 60.09-26.07 89.67-39.8 40.92-19 84.73-46 130.83-49.67 36.26-2.85 70.9 9.42 98.6 31.56 31.77 25.39 62.32 62 103.63 73 40.44 10.79 81.35-6.69 119.13-24.28s75.16-39 116.92-43.05c59.73-5.85 113.28 22.88 168.9 38.84 30.2 8.66 59 6.17 87.09-7.5 22.43-10.89 48-26.93 60.65-49.24V0z"
            opacity=".5"
            />
            <path d="M0 0v5.63C149.93 59 314.09 71.32 475.83 42.57c43-7.64 84.23-20.12 127.61-26.46 59-8.63 112.48 12.24 165.56 35.4C827.93 77.22 886 95.24 951.2 90c86.53-7 172.46-45.71 248.8-84.81V0z" />
        </svg>
        </div>
</header>
<main>
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    
    <div class="wrapper">
        <section>
            <?php the_content(); ?>
        </section>
        <?php endwhile; else: endif; ?>
    </div>
</main>

<script>
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    const snowflakes = document.getElementsByClassName("snowflake");

    const style1 = window.getComputedStyle(document.getElementById("flake1"));
    document.getElementById("flake1").dataset.top = parseInt(style1.getPropertyValue('top').replace('px', ''));
    const style2 = window.getComputedStyle(document.getElementById("flake2"));
    document.getElementById("flake2").dataset.top = parseInt(style2.getPropertyValue('top').replace('px', ''));
    const style3 = window.getComputedStyle(document.getElementById("flake3"));
    document.getElementById("flake3").dataset.top = parseInt(style3.getPropertyValue('top').replace('px', ''));
    const style4 = window.getComputedStyle(document.getElementById("flake4"));
    document.getElementById("flake4").dataset.top = parseInt(style4.getPropertyValue('top').replace('px', ''));

    if (!reducedMotion) {
        window.addEventListener("scroll", function() {
            for(flake of snowflakes) {
                spin = parseInt(flake.dataset.spin);

                flake.animate({
                    transform: "rotate(" + window.pageYOffset / spin + "deg)",
                    top: (window.pageYOffset + parseInt(flake.dataset.top)) + "px"
                }, {
                    duration: 1000,
                    fill: "forwards",
                    easing: "ease-out"
                })

                // flake.style.transform = "rotate(" + window.pageYOffset / spin + "deg)";
                // flake.style.top = (window.pageYOffset + parseInt(flake.dataset.top)) + "px";
            };
        });
    }

</script>

<?php
get_footer();
?>
