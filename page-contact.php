<?php
/**
 * Template Name: Contact
 *
 * Contact page with a contact form, address/map section and social links.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

get_header();

$contact_email   = get_theme_mod('contact_email', get_option('admin_email'));
$contact_phone   = get_theme_mod('contact_phone', '');
$contact_address = get_theme_mod('contact_address', '');
$map_embed_url   = get_theme_mod('contact_map_embed_url', '');
?>

<main id="main-content" class="site-main">
    <div class="container">

        <header class="page-header">
            <?php the_title('<h1 class="page-title">', '</h1>'); ?>
            <?php the_content(); ?>
        </header>

        <div class="contact-layout">

            <!-- Contact Form Column -->
            <div class="contact-layout__form">

                <div class="card">
                    <div class="card__body">
                        <h2 class="contact-form__heading">
                            <?php esc_html_e('Send us a message', 'theme-associatif'); ?>
                        </h2>

                        <?php
                        // Use Contact Form 7 shortcode if available, otherwise render a native form.
                        if (shortcode_exists('contact-form-7')) :
                            $cf7_id = get_theme_mod('contact_cf7_id', '');

                            if (!empty($cf7_id)) :
                                echo do_shortcode('[contact-form-7 id="' . esc_attr($cf7_id) . '"]');
                            else :
                                esc_html_e('Please configure the Contact Form 7 ID in the Customizer.', 'theme-associatif');
                            endif;
                        else :
                        ?>
                            <form
                                class="contact-form"
                                method="post"
                                action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                                novalidate
                                aria-label="<?php esc_attr_e('Contact form', 'theme-associatif'); ?>"
                            >
                                <?php wp_nonce_field('theme_associatif_contact', 'contact_nonce'); ?>
                                <input type="hidden" name="action" value="theme_associatif_contact">

                                <div class="form-group">
                                    <label for="contact-name" class="form-label form-label--required">
                                        <?php esc_html_e('Full name', 'theme-associatif'); ?>
                                    </label>
                                    <input
                                        type="text"
                                        id="contact-name"
                                        name="contact_name"
                                        class="form-control"
                                        required
                                        autocomplete="name"
                                        aria-required="true"
                                    />
                                </div>

                                <div class="form-group">
                                    <label for="contact-email" class="form-label form-label--required">
                                        <?php esc_html_e('Email address', 'theme-associatif'); ?>
                                    </label>
                                    <input
                                        type="email"
                                        id="contact-email"
                                        name="contact_email"
                                        class="form-control"
                                        required
                                        autocomplete="email"
                                        aria-required="true"
                                    />
                                </div>

                                <div class="form-group">
                                    <label for="contact-subject" class="form-label">
                                        <?php esc_html_e('Subject', 'theme-associatif'); ?>
                                    </label>
                                    <input
                                        type="text"
                                        id="contact-subject"
                                        name="contact_subject"
                                        class="form-control"
                                    />
                                </div>

                                <div class="form-group">
                                    <label for="contact-message" class="form-label form-label--required">
                                        <?php esc_html_e('Message', 'theme-associatif'); ?>
                                    </label>
                                    <textarea
                                        id="contact-message"
                                        name="contact_message"
                                        class="form-control"
                                        rows="6"
                                        required
                                        aria-required="true"
                                    ></textarea>
                                </div>

                                <button type="submit" class="btn btn--primary btn--lg">
                                    <?php esc_html_e('Send message', 'theme-associatif'); ?>
                                </button>
                            </form>
                        <?php endif; ?>

                    </div>
                </div>

            </div><!-- .contact-layout__form -->

            <!-- Contact Info Column -->
            <aside class="contact-layout__info" aria-label="<?php esc_attr_e('Contact information', 'theme-associatif'); ?>">

                <div class="card contact-info">
                    <div class="card__body">
                        <h2 class="contact-info__heading">
                            <?php esc_html_e('Contact information', 'theme-associatif'); ?>
                        </h2>

                        <ul class="contact-info__list">
                            <?php if (!empty($contact_email)) : ?>
                                <li class="contact-info__item">
                                    <svg class="contact-info__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                    <a href="mailto:<?php echo esc_attr($contact_email); ?>">
                                        <?php echo esc_html($contact_email); ?>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if (!empty($contact_phone)) : ?>
                                <li class="contact-info__item">
                                    <svg class="contact-info__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.09h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 5.95 5.95l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                    </svg>
                                    <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $contact_phone)); ?>">
                                        <?php echo esc_html($contact_phone); ?>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if (!empty($contact_address)) : ?>
                                <li class="contact-info__item">
                                    <svg class="contact-info__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <address class="contact-info__address">
                                        <?php echo nl2br(esc_html($contact_address)); ?>
                                    </address>
                                </li>
                            <?php endif; ?>
                        </ul>

                        <!-- Social Links -->
                        <?php
                        $social_items = array(
                            'github'    => array('label' => 'GitHub',     'mod' => 'social_github'),
                            'twitter'   => array('label' => 'Twitter/X',  'mod' => 'social_twitter'),
                            'instagram' => array('label' => 'Instagram',  'mod' => 'social_instagram'),
                            'linkedin'  => array('label' => 'LinkedIn',   'mod' => 'social_linkedin'),
                            'facebook'  => array('label' => 'Facebook',   'mod' => 'social_facebook'),
                        );

                        $has_social = false;
                        foreach ($social_items as $item) {
                            if (!empty(get_theme_mod($item['mod']))) {
                                $has_social = true;
                                break;
                            }
                        }

                        if ($has_social) :
                        ?>
                            <div class="contact-info__social site-footer__social" aria-label="<?php esc_attr_e('Social media links', 'theme-associatif'); ?>">
                                <?php foreach ($social_items as $key => $item) :
                                    $url = get_theme_mod($item['mod'], '');

                                    if (empty($url)) :
                                        continue;
                                    endif;
                                ?>
                                    <a
                                        href="<?php echo esc_url($url); ?>"
                                        class="social-link"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        aria-label="<?php echo esc_attr($item['label']); ?>"
                                    >
                                        <span class="sr-only"><?php echo esc_html($item['label']); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- Map Embed -->
                <?php if (!empty($map_embed_url)) : ?>
                    <div class="contact-map card" aria-label="<?php esc_attr_e('Location map', 'theme-associatif'); ?>">
                        <iframe
                            class="contact-map__iframe"
                            src="<?php echo esc_url($map_embed_url); ?>"
                            width="100%"
                            height="300"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="<?php esc_attr_e('Association location map', 'theme-associatif'); ?>"
                        ></iframe>
                    </div>
                <?php endif; ?>

            </aside><!-- .contact-layout__info -->

        </div><!-- .contact-layout -->

    </div>
</main>

<?php get_footer(); ?>
