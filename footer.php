    </div><!-- #content -->

    <footer id="site-footer" class="site-footer" role="contentinfo">

        <div class="site-footer__main">

            <!-- Brand and social column -->
            <div class="site-footer__brand">
                <?php if (has_custom_logo()) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <span class="site-footer__site-name"><?php bloginfo('name'); ?></span>
                <?php endif; ?>

                <?php
                $footer_description = get_theme_mod('footer_description', '');
                if (!empty($footer_description)) :
                ?>
                    <p class="site-footer__description">
                        <?php echo esc_html($footer_description); ?>
                    </p>
                <?php else : ?>
                    <p class="site-footer__description">
                        <?php bloginfo('description'); ?>
                    </p>
                <?php endif; ?>

                <!-- Social Links -->
                <div class="site-footer__social" aria-label="<?php esc_attr_e('Social media links', 'theme-associatif'); ?>">
                    <?php
                    $social_links = array(
                        'github'    => array(
                            'label' => __('GitHub', 'theme-associatif'),
                            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false" fill="currentColor"><path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0 1 12 6.844a9.59 9.59 0 0 1 2.504.337c1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0 0 22 12.017C22 6.484 17.522 2 12 2z"/></svg>',
                        ),
                        'twitter'   => array(
                            'label' => __('Twitter / X', 'theme-associatif'),
                            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.736-8.859L1.254 2.25H8.08l4.258 5.63 5.906-5.63zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
                        ),
                        'instagram' => array(
                            'label' => __('Instagram', 'theme-associatif'),
                            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>',
                        ),
                        'linkedin'  => array(
                            'label' => __('LinkedIn', 'theme-associatif'),
                            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>',
                        ),
                        'facebook'  => array(
                            'label' => __('Facebook', 'theme-associatif'),
                            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>',
                        ),
                    );

                    foreach ($social_links as $key => $social) :
                        $url = get_theme_mod('social_' . $key, '');

                        if (empty($url)) :
                            continue;
                        endif;
                    ?>
                        <a
                            href="<?php echo esc_url($url); ?>"
                            class="social-link"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="<?php echo esc_attr($social['label']); ?>"
                        >
                            <?php echo $social['icon']; // SVG icon markup. ?>
                        </a>
                    <?php endforeach; ?>
                </div><!-- .site-footer__social -->
            </div><!-- .site-footer__brand -->

            <!-- Navigation Column 1 -->
            <nav class="site-footer__nav-group" aria-label="<?php esc_attr_e('Footer navigation column 1', 'theme-associatif'); ?>">
                <h3 class="site-footer__nav-heading"><?php esc_html_e('Association', 'theme-associatif'); ?></h3>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer-col-1',
                    'menu_class'     => 'site-footer__nav-list',
                    'container'      => false,
                    'depth'          => 1,
                    'fallback_cb'    => static function () {
                        echo '<ul class="site-footer__nav-list">';
                        echo '<li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('Home', 'theme-associatif') . '</a></li>';
                        echo '</ul>';
                    },
                ));
                ?>
            </nav>

            <!-- Navigation Column 2 -->
            <nav class="site-footer__nav-group" aria-label="<?php esc_attr_e('Footer navigation column 2', 'theme-associatif'); ?>">
                <h3 class="site-footer__nav-heading"><?php esc_html_e('Resources', 'theme-associatif'); ?></h3>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer-col-2',
                    'menu_class'     => 'site-footer__nav-list',
                    'container'      => false,
                    'depth'          => 1,
                    'fallback_cb'    => false,
                ));
                ?>
            </nav>

            <!-- Navigation Column 3 -->
            <nav class="site-footer__nav-group" aria-label="<?php esc_attr_e('Footer navigation column 3', 'theme-associatif'); ?>">
                <h3 class="site-footer__nav-heading"><?php esc_html_e('Contact', 'theme-associatif'); ?></h3>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer',
                    'menu_class'     => 'site-footer__nav-list',
                    'container'      => false,
                    'depth'          => 1,
                    'fallback_cb'    => false,
                ));
                ?>
            </nav>

        </div><!-- .site-footer__main -->

        <!-- Copyright Bar -->
        <div class="site-footer__bar">
            <div class="site-footer__bar-inner">
                <p class="site-footer__copyright">
                    &copy; <?php echo esc_html(gmdate('Y')); ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>.
                    <?php
                    $footer_text = get_theme_mod('footer_copyright_text', '');
                    if (!empty($footer_text)) :
                        echo esc_html($footer_text);
                    else :
                        esc_html_e('All rights reserved.', 'theme-associatif');
                    endif;
                    ?>
                </p>

                <ul class="site-footer__legal-links">
                    <?php
                    $privacy_url = get_privacy_policy_url();
                    if ($privacy_url) :
                    ?>
                        <li>
                            <a href="<?php echo esc_url($privacy_url); ?>">
                                <?php esc_html_e('Privacy Policy', 'theme-associatif'); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div><!-- .site-footer__bar -->

    </footer><!-- #site-footer -->

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
