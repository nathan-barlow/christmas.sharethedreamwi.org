
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
        <section>
            <?php the_content(); ?>
        </section>
        <section class="grid grid-3">
            <div class="span-2 floating-card" style="overflow:hidden;width:100%;height:1200px;">
                <h4>Donate Online</h4>
                <iframe title='Donation form powered by Zeffy' style='width:100%;height:100%;border:0;' src='https://www.zeffy.com/en-US/embed/donation-form/9b8fcf11-99fa-4e50-806e-8c1fefd99426' allowpaymentrequest allowTransparency="true"></iframe>
            </div>
            <div class="grid donate-options">
                <div class="floating-card">
                    <h4 class="wp-block-heading">Write a Check</h4>
                    <p>Please make checks payable to <b>Share the Dream</b></p>
                    <p>
                        <strong>Mail to</strong>:<br>
                        Share the Dream<br>
                        N250 Eastowne Lane<br>
                        Appleton, WI 54915
                    </p>
                </div>
                <div class="floating-card">
                    <h4>Amazon Wishlist</h4>
                    <p>Help our cause by gifting something from our Amazon Wishlist.</p>
                    <a class="button" href="https://www.walmart.com/registry/ER/e9718a4b-77ff-4684-b374-67804a54648a" target="_blank">View our wishlist</a>
                </div>
                <div class="floating-card">
                    <h4>Walmart Registry</h4>
                    <p>Help our cause by gifting something from our Walmart Registry.</p>
                    <a class="button" href="https://www.amazon.com/registries/gl/guest-view/C8KQD2CQC89A" target="_blank">Open the registry</a>
                </div>
            </div>
        </section>
        <?php endwhile; else: endif; ?>
    </div>
</main>

<?php
get_footer();
?>
