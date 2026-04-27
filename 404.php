<?php
/**
 * WordPress 404 Error Template
 *
 * Displays a friendly error message with a search form.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

get_header();
?>

<main id="main-content" class="site-main">
    <div class="container">

        <section class="error-404" aria-labelledby="error-404-heading">

            <div class="error-404__content">
                <span class="error-404__code" aria-hidden="true">404</span>

                <h1 id="error-404-heading" class="error-404__title">
                    <?php esc_html_e('Page not found', 'theme-associatif'); ?>
                </h1>

                <p class="error-404__description">
                    <?php esc_html_e('Sorry, the page you are looking for does not exist or has been moved. Try searching below or return to the home page.', 'theme-associatif'); ?>
                </p>

                <div class="error-404__search">
                    <?php get_search_form(); ?>
                </div>

                <div class="error-404__actions">
                    <?php
                    get_template_part('template-parts/components/button', null, array(
                        'text'    => __('Go back home', 'theme-associatif'),
                        'url'     => home_url('/'),
                        'variant' => 'primary',
                        'size'    => 'lg',
                    ));
                    ?>
                </div>
            </div>

        </section>

    </div>
</main>

<?php get_footer(); ?>
