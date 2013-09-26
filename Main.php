<?php
/*
Plugin Name: Dish
Description: Plugin to manage and present all your delicious Dishes
Author: Marc Neuhaus <mneuhaus@famelo.com>
Version: 0.0.1
*/

class FameloDish {
	public function __construct() {
		add_action('init', array($this, 'init'));
		add_filter('template_include', array( $this, 'template_include' ) );
	}

	public function init() {
		register_post_type('dish',
			array(
				'labels' => array(
					'name' => 'Dishes',
					'singular_name' => 'dish',
					'add_new' => 'Add New',
					'add_new_item' => 'Add New Dish',
					'edit' => 'Edit',
					'edit_item' => 'Edit Dish',
					'new_item' => 'New Dish',
					'view' => 'View',
					'view_item' => 'View Dish',
					'search_items' => 'Search Dish',
					'not_found' => 'No Dishes found',
					'not_found_in_trash' => 'No Dishes found in Trash',
					'parent' => 'Parent Dish'
				),

				'public' => true,
				'menu_position' => 15,
				'supports' => array('title', 'editor'),
				'taxonomies' => array(''),
				// 'menu_icon' => plugins_url( 'images/image.png', __FILE__ ),
				'has_archive' => true
			)
		);

		// Add new "Locations" taxonomy to Posts
		register_taxonomy('dish_category', 'dish', array(
			'hierarchical' => true,
			'labels' => array(
				'name' => _x( 'Dish Categories', 'taxonomy general name' ),
				'singular_name' => _x( 'Dish Category', 'taxonomy singular name' ),
				'search_items' =>  __( 'Search Dish Categories' ),
				'all_items' => __( 'All Dish Categories' ),
				'parent_item' => __( 'Parent Dish Category' ),
				'parent_item_colon' => __( 'Parent Dish Category:' ),
				'edit_item' => __( 'Edit Dish Category' ),
				'update_item' => __( 'Update Dish Category' ),
				'add_new_item' => __( 'Add New Dish Category' ),
				'new_item_name' => __( 'New Dish Category Name' ),
				'menu_name' => __( 'Dish Categories' ),
			),
			// Control the slugs used for this taxonomy
			'rewrite' => array(
				'slug' => 'dish-category', // This controls the base slug that will display before each term
				'with_front' => false, // Don't display the category base before "/locations/"
				'hierarchical' => true // This will allow URL's like "/locations/boston/cambridge/"
			),
		));
	}

	public function template_include( $templatePath ) {
		$object = get_queried_object();
		if (isset($object->term_id)) {
			if ($object->taxonomy === 'dish_category') {
				$templateName = "taxonomy-{$object->taxonomy}.php";
				if ( $themeFile = locate_template(array('templates/' . $templateName, $templateName))) {
					$templatePath = $themeFile;
				} else {
					$templatePath = plugin_dir_path( __FILE__ ) . 'templates/' . $templateName;
				}
			}
		}
		if ( get_post_type() == 'dish' ) {
			if ( is_single() ) {
				$templateName = "single-dish.php";
				if ( $themeFile = locate_template(array('templates/' . $templateName, $templateName))) {
					$templatePath = $themeFile;
				} else {
					$templatePath = plugin_dir_path( __FILE__ ) . 'templates/' . $templateName;
				}
			}
		}
		return $templatePath;
	}
}

function famelo_dish_get_template($templateName) {
	$templateName.= '.php';
	if ( $themeFile = locate_template(array('templates/' . $templateName, $templateName))) {
		$templatePath = $themeFile;
	} else {
		$templatePath = plugin_dir_path( __FILE__ ) . 'templates/' . $templateName;
	}
	return $templatePath;
}

new FameloDish();

?>