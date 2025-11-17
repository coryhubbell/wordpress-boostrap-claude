<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <header class="site-header">
        <div class="container">
            <div class="site-branding">
                <?php if (is_front_page() && is_home()) : ?>
                    <h1 class="site-title">
                        <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                            <?php bloginfo('name'); ?>
                        </a>
                    </h1>
                <?php else : ?>
                    <p class="site-title">
                        <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                            <?php bloginfo('name'); ?>
                        </a>
                    </p>
                <?php endif; ?>

                <?php
                $description = get_bloginfo('description', 'display');
                if ($description || is_customize_preview()) :
                ?>
                    <p class="site-description"><?php echo $description; ?></p>
                <?php endif; ?>
            </div>

            <nav class="site-nav">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container'      => false,
                    'fallback_cb'    => false,
                ]);
                ?>
            </nav>
        </div>
    </header>

    <main class="site-content">
        <div class="container">
            <?php if (have_posts()) : ?>

                <?php if (is_home() && !is_front_page()) : ?>
                    <header class="page-header">
                        <h1 class="page-title"><?php single_post_title(); ?></h1>
                    </header>
                <?php endif; ?>

                <?php
                // Start the Loop.
                while (have_posts()) :
                    the_post();
                ?>

                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <header class="entry-header">
                            <?php
                            if (is_singular()) :
                                the_title('<h1 class="entry-title">', '</h1>');
                            else :
                                the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');
                            endif;
                            ?>

                            <?php if (!is_page()) : ?>
                                <div class="entry-meta">
                                    <span class="posted-on">
                                        <?php echo get_the_date(); ?>
                                    </span>
                                    <span class="byline">
                                        <?php _e('by', 'wpbc'); ?> <?php the_author(); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </header>

                        <div class="entry-content">
                            <?php
                            if (is_singular()) :
                                the_content();

                                wp_link_pages([
                                    'before' => '<div class="page-links">' . __('Pages:', 'wpbc'),
                                    'after'  => '</div>',
                                ]);
                            else :
                                the_excerpt();
                            endif;
                            ?>
                        </div>

                        <?php if (is_singular()) : ?>
                            <footer class="entry-footer">
                                <?php
                                // Show categories and tags
                                $categories_list = get_the_category_list(', ');
                                if ($categories_list) {
                                    printf('<span class="cat-links">' . __('Posted in %s', 'wpbc') . '</span>', $categories_list);
                                }

                                $tags_list = get_the_tag_list('', ', ');
                                if ($tags_list) {
                                    printf('<span class="tags-links">' . __('Tagged %s', 'wpbc') . '</span>', $tags_list);
                                }
                                ?>
                            </footer>
                        <?php endif; ?>
                    </article>

                <?php endwhile; ?>

                <?php
                // Previous/next page navigation.
                the_posts_pagination([
                    'prev_text'          => __('Previous', 'wpbc'),
                    'next_text'          => __('Next', 'wpbc'),
                    'before_page_number' => '<span class="meta-nav screen-reader-text">' . __('Page', 'wpbc') . ' </span>',
                ]);
                ?>

            <?php else : ?>

                <div class="no-results not-found">
                    <header class="page-header">
                        <h1 class="page-title"><?php _e('Nothing Found', 'wpbc'); ?></h1>
                    </header>

                    <div class="page-content">
                        <?php if (is_home() && current_user_can('publish_posts')) : ?>
                            <p>
                                <?php
                                printf(
                                    __('Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'wpbc'),
                                    esc_url(admin_url('post-new.php'))
                                );
                                ?>
                            </p>
                        <?php elseif (is_search()) : ?>
                            <p><?php _e('Sorry, but nothing matched your search terms. Please try again with different keywords.', 'wpbc'); ?></p>
                            <?php get_search_form(); ?>
                        <?php else : ?>
                            <p><?php _e('It seems we can\'t find what you\'re looking for. Perhaps searching can help.', 'wpbc'); ?></p>
                            <?php get_search_form(); ?>
                        <?php endif; ?>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <?php
            if (has_nav_menu('footer')) :
                wp_nav_menu([
                    'theme_location' => 'footer',
                    'container'      => 'nav',
                    'container_class'=> 'footer-nav',
                    'fallback_cb'    => false,
                ]);
            endif;
            ?>

            <div class="site-info">
                <p>
                    <?php
                    printf(
                        __('Powered by <a href="%s">WordPress Bootstrap Claude</a>', 'wpbc'),
                        'https://github.com/coryhubbell/wordpress-bootstrap-claude'
                    );
                    ?>
                    &mdash;
                    <?php
                    printf(
                        __('Translation Bridge™ v%s', 'wpbc'),
                        WPBC_THEME_VERSION
                    );
                    ?>
                </p>
            </div>

            <?php if (is_active_sidebar('footer-1')) : ?>
                <div class="footer-widgets">
                    <?php dynamic_sidebar('footer-1'); ?>
                </div>
            <?php endif; ?>
        </div>
    </footer>
</div>

<?php wp_footer(); ?>
</body>
</html>
