<?php get_header(); ?>
    <?php
    $mypost = array( 'post_type' => 'pluginname', );
    $loop = new WP_Query( $mypost );
    ?>
    <?php while ( $loop->have_posts() ) : $loop->the_post();?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header>
                <?php the_post_thumbnail(); ?>
                <?php the_title(); ?>
            </header>
            <?php the_content(); ?>
        </article>
    <?php endwhile; ?>
<?php wp_reset_query(); ?>
<?php get_footer(); ?>