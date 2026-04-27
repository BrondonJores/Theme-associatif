<?php
/**
 * Template Name: Members
 *
 * Members listing page displaying profile cards in a responsive grid.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

get_header();

// Retrieve members (WordPress users with the 'subscriber' or higher role).
$members_args = array(
    'role__in' => array('administrator', 'editor', 'author', 'contributor', 'subscriber'),
    'orderby'  => 'display_name',
    'order'    => 'ASC',
    'number'   => 50,
);

// Allow filtering members by search query.
$search_term = isset($_GET['member_search']) ? sanitize_text_field(wp_unslash($_GET['member_search'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

if (!empty($search_term)) {
    $members_args['search']         = '*' . $search_term . '*';
    $members_args['search_columns'] = array('user_login', 'user_nicename', 'display_name', 'user_email');
}

$members = get_users($members_args);
?>

<main id="main-content" class="site-main">
    <div class="container">

        <header class="page-header">
            <?php the_title('<h1 class="page-title">', '</h1>'); ?>
            <?php the_content(); ?>
        </header>

        <!-- Member Search -->
        <div class="members-filter">
            <form class="members-filter__form" method="get" action="<?php echo esc_url(get_permalink()); ?>">
                <div class="form-group">
                    <label for="member_search" class="form-label">
                        <?php esc_html_e('Search members', 'theme-associatif'); ?>
                    </label>
                    <div class="members-filter__input-wrap">
                        <input
                            type="search"
                            id="member_search"
                            name="member_search"
                            class="form-control"
                            placeholder="<?php esc_attr_e('Name or username...', 'theme-associatif'); ?>"
                            value="<?php echo esc_attr($search_term); ?>"
                            aria-label="<?php esc_attr_e('Search members by name', 'theme-associatif'); ?>"
                        />
                        <button type="submit" class="btn btn--primary">
                            <?php esc_html_e('Search', 'theme-associatif'); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <?php if (!empty($members)) : ?>

            <div class="grid grid-cols-2 grid-cols-3@md grid-cols-4@lg gap-6">
                <?php foreach ($members as $member) : ?>
                    <article class="member-card card" aria-labelledby="member-<?php echo esc_attr($member->ID); ?>-name">

                        <div class="member-card__avatar">
                            <?php
                            echo get_avatar(
                                $member->ID,
                                120,
                                '',
                                esc_attr($member->display_name),
                                array('class' => 'member-card__avatar-img')
                            );
                            ?>
                        </div>

                        <div class="member-card__body card__body">
                            <h2
                                id="member-<?php echo esc_attr($member->ID); ?>-name"
                                class="member-card__name"
                            >
                                <?php echo esc_html($member->display_name); ?>
                            </h2>

                            <?php
                            $role_names = array();
                            $user_roles = (array) $member->roles;

                            foreach ($user_roles as $role_key) {
                                $wp_roles = wp_roles();
                                if (isset($wp_roles->roles[$role_key])) {
                                    $role_names[] = translate_user_role($wp_roles->roles[$role_key]['name']);
                                }
                            }

                            if (!empty($role_names)) :
                            ?>
                                <span class="member-card__role badge badge--primary badge--sm">
                                    <?php echo esc_html(implode(', ', $role_names)); ?>
                                </span>
                            <?php endif; ?>

                            <?php
                            $bio = get_the_author_meta('description', $member->ID);
                            if (!empty($bio)) :
                            ?>
                                <p class="member-card__bio">
                                    <?php echo esc_html(wp_trim_words($bio, 15)); ?>
                                </p>
                            <?php endif; ?>

                            <?php
                            $author_url = get_author_posts_url($member->ID);
                            if ($author_url) :
                            ?>
                                <a
                                    href="<?php echo esc_url($author_url); ?>"
                                    class="member-card__link"
                                    aria-label="<?php echo esc_attr(sprintf(__('View posts by %s', 'theme-associatif'), $member->display_name)); ?>"
                                >
                                    <?php esc_html_e('View posts', 'theme-associatif'); ?>
                                    <span aria-hidden="true">&rarr;</span>
                                </a>
                            <?php endif; ?>
                        </div>

                    </article>
                <?php endforeach; ?>
            </div>

        <?php else : ?>

            <p class="members-empty">
                <?php esc_html_e('No members found.', 'theme-associatif'); ?>
            </p>

        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>
