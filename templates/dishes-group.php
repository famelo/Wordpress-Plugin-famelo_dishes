<div>
	<a href="<?=$category->link?>"><?=$category->name?></a>
	<?php
		while (have_posts()){
			the_post();
			get_template_part('templates/content', 'dishes');
		}
	?>
</div>