<?php get_header(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header>
        <?php the_post_thumbnail(); ?>
        <?php the_title(); ?>
    </header>
    <?php the_content(); ?>
</article>
<?php get_footer(); ?>