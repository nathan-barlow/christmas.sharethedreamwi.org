
    <footer>
        <div class="grid grid-3">
            <div class="footer-main">
                <img src="/wp-content/uploads/2023/07/logo-white.png" alt="Community Christmas Logo">

                <div>
                    <a href="/">
                        &copy; <?php echo date('Y'); ?> Share the Dream, Inc
                    </a>
                    
                    <a href="http://www.natebarlow.me" target="_blank">
                        Website designed/developed by <u>Nate Barlow</u>
                    </a>
                </div>
            </div>
            <div id="footer-contact" class="footer-contact">
                <h4>Contact Us</h4>

                <ul>
                    <li class="email">
                        <a href="mailto:communitychristmasfoxcities@gmail.com">communitychristmasfoxcities@gmail.com
                        </a>
                    </li>
                    <li class="phone">
                        <a href="tel:+19205742199">(920) 574-2199
                        </a>
                    </li>
                    <li class="address">
                        <address>
                            N250 Eastowne Lane <br>
                            Appleton, WI 54915
                        </address>
                    </li>
                </ul>
            </div>
            <div class="footer-nav">
                <h4>Navigate</h4>
                <?php
                    $footer_nav = array(
                        'theme_location' => 'nav-footer',
                        'container' => 'nav',
                        'container_class' => 'nav-footer',
                        'depth' => 3
                    );
                    wp_nav_menu( $footer_nav );
                ?>
            </div>
        </div>
    </footer>

<?php
wp_footer();
?>

</body>

</html>
