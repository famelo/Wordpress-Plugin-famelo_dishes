<?php
global $wp_query;
$object = get_queried_object();
$displayType = get_field('displayType', 'dish_category_' . $object->term_id);
?>

<?php get_template_part('templates/page', 'header'); ?>

<?php if (!have_posts()) : ?>
  <div class="alert">
    <?php _e('Sorry, no results were found.', 'roots'); ?>
  </div>
  <?php get_search_form(); ?>
<?php endif; ?>

<?php
	if ($displayType == 'Dishes') {
		while (have_posts()){
			the_post();
			include(famelo_dish_get_template('dishes-default'));
		}
	} else {
		$taxonomy = $object->taxonomy;
		$categories = get_term_children($object->term_id, $taxonomy);
		foreach ($categories as $key => $categoryId) {
			$category = get_term($categoryId, $taxonomy);
			$category->link = get_term_link($category, $taxonomy);
			if ($displayType === 'Subcategories') {
				include(famelo_dish_get_template('dishes-category'));
			}
			if ($displayType === 'Grouped') {
				$wp_query = new WP_Query(array($taxonomy => $category->slug));
				include(famelo_dish_get_template('dishes-group'));
			}
		}
	}
?>

<?php if ($wp_query->max_num_pages > 1) : ?>
  <nav class="post-nav">
    <ul class="pager">
      <li class="previous"><?php next_posts_link(__('&larr; Older posts', 'roots')); ?></li>
      <li class="next"><?php previous_posts_link(__('Newer posts &rarr;', 'roots')); ?></li>
    </ul>
  </nav>
<?php endif; ?>