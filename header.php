<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">

    <a class="skip-to-content" href="#main-content">
        <?php esc_html_e('Skip to content', 'theme-associatif'); ?>
    </a>

    <header id="site-header" class="site-header" role="banner">
        <div class="site-header__inner">

            <!-- Brand / Logo -->
            <div class="site-header__brand-wrap">
                <?php if (has_custom_logo()) : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-header__brand" rel="home">
                        <?php the_custom_logo(); ?>
                        <span class="site-header__logo-text">
                            <span class="site-header__site-name"><?php bloginfo('name'); ?></span>
                            <?php
                            $tagline = get_bloginfo('description', 'display');
                            if ($tagline) :
                            ?>
                                <span class="site-header__tagline"><?php echo esc_html($tagline); ?></span>
                            <?php endif; ?>
                        </span>
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-header__brand" rel="home">
                        <span class="site-header__logo-text">
                            <span class="site-header__site-name"><?php bloginfo('name'); ?></span>
                            <?php
                            $tagline = get_bloginfo('description', 'display');
                            if ($tagline) :
                            ?>
                                <span class="site-header__tagline"><?php echo esc_html($tagline); ?></span>
                            <?php endif; ?>
                        </span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Primary Navigation -->
            <nav id="site-nav" class="site-header__nav site-nav" role="navigation" aria-label="<?php esc_attr_e('Primary navigation', 'theme-associatif'); ?>">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'menu_class'     => 'primary-menu',
                    'container'      => false,
                    'fallback_cb'    => false,
                ));
                ?>
            </nav>

            <!-- Header Actions -->
            <div class="site-header__actions">

                <!-- Dark Mode Toggle -->
                <button
                    type="button"
                    class="theme-toggle"
                    aria-label="<?php esc_attr_e('Switch to dark mode', 'theme-associatif'); ?>"
                    data-label-dark="<?php esc_attr_e('Switch to dark mode', 'theme-associatif'); ?>"
                    data-label-light="<?php esc_attr_e('Switch to light mode', 'theme-associatif'); ?>"
                >
                    <!-- Sun icon (shown in dark mode) -->
                    <svg class="theme-toggle__icon icon-sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                    <!-- Moon icon (shown in light mode) -->
                    <svg class="theme-toggle__icon icon-moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>

                <!-- Hamburger (mobile only) -->
                <button
                    id="nav-toggle"
                    type="button"
                    class="nav-toggle"
                    aria-controls="mobile-menu"
                    aria-expanded="false"
                    aria-label="<?php esc_attr_e('Open navigation menu', 'theme-associatif'); ?>"
                >
                    <span class="hamburger" aria-hidden="true">
                        <span class="hamburger__line"></span>
                        <span class="hamburger__line"></span>
                        <span class="hamburger__line"></span>
                    </span>
                </button>

            </div>

        </div><!-- .site-header__inner -->

        <!-- Mobile Navigation Drawer -->
        <nav
            id="mobile-menu"
            class="mobile-menu"
            role="navigation"
            aria-label="<?php esc_attr_e('Mobile navigation', 'theme-associatif'); ?>"
            hidden
        >
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'menu_class'     => 'mobile-menu__list',
                'container'      => false,
                'fallback_cb'    => false,
            ));
            ?>
        </nav>

        <div id="mobile-menu-backdrop" class="mobile-menu-backdrop" aria-hidden="true"></div>

    </header><!-- #site-header -->

    <div id="content" class="site-content">
