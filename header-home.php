
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Google Font IBM Plex Serif -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Serif:wght@400;600&display=swap" rel="stylesheet">

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-11348359547"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'AW-11348359547');

        // Page View Conversion
        gtag('event', 'conversion', {'send_to': 'AW-11348359547/nFU8COPjp-UYEPvyqKMq'});
    </script>

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <header class="overflow-hidden" <?php 
        $background_image = get_the_post_thumbnail_url();
        if($background_image) {
        echo("style=\"background-image: url('" . $background_image . "'), var(--gradient);\"");
    } ?> >
        <nav class="full-image-header">
            <div class="header-content">
                <a class="header-link" href="<?php echo esc_url(home_url()) ?>">
                    <img
                        id="custom-header"
                        src="/wp-content/uploads/2023/09/std-logo-cream.png"
                        height="<?php echo get_custom_header()->height; ?>"
                        width="<?php echo get_custom_header()->width; ?>"
                        alt="<?php bloginfo( 'description' ); ?>"
                    >
                </a>
            </div>

            <input type="checkbox" class="nav-main-menu-toggle" id="nav-main-menu-toggle">
            <label class="nav-main-menu-toggle-icon" for="nav-main-menu-toggle">
                <span></span>
            </label>

            <div class="nav-main-drawer">
                    <!-- Main Menu -->
                    <?php
                    $nav_main_header_top = array(
                        'theme_location' => 'nav-main-header-top',
                        'container_class' => 'nav-main',
                        'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                        'depth' => 2
                    );
                    wp_nav_menu( $nav_main_header_top );
                ?>
            </div>
        </nav>
        
        