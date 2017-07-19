<?php

namespace Roots\Sage\CPT;

add_action( 'init', function() {
	register_post_type( 'election',
		array('labels' => array(
				'name' => 'Elections',
				'singular_name' => 'Election',
				'add_new' => 'Add New',
				'add_new_item' => 'Add New Election',
				'edit' => 'Edit',
				'edit_item' => 'Edit Election',
				'new_item' => 'New Election',
				'view_item' => 'View Election',
				'search_items' => 'Search Elections',
				'not_found' =>  'Nothing found in the Database.',
				'not_found_in_trash' => 'Nothing found in Trash',
				'parent_item_colon' => ''
			), /* end of arrays */
			'exclude_from_search' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_nav_menus' => false,
			'menu_position' => 8,
			//'menu_icon' => get_stylesheet_directory_uri() . '/library/images/custom-post-icon.png',
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => array('revisions'),
			'has_archive' => false,
			'rewrite' => true,
			'query_var' => true
		)
	);

	register_post_type( 'ballot',
		array('labels' => array(
				'name' => 'Ballots',
				'singular_name' => 'Ballot',
				'add_new' => 'Add New',
				'add_new_item' => 'Add New Ballot',
				'edit' => 'Edit',
				'edit_item' => 'Edit Ballot',
				'new_item' => 'New Ballot',
				'view_item' => 'View Ballot',
				'search_items' => 'Search Ballots',
				'not_found' =>  'Nothing found in the Database.',
				'not_found_in_trash' => 'Nothing found in Trash',
				'parent_item_colon' => ''
			), /* end of arrays */
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_nav_menus' => false,
			'menu_position' => 8,
			//'menu_icon' => get_stylesheet_directory_uri() . '/library/images/custom-post-icon.png',
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => array( 'revisions'),
			'has_archive' => false,
			'rewrite' => false,
			'query_var' => true
		)
	);

	register_post_type( 'exit-poll',
		array('labels' => array(
				'name' => 'Exit Polls',
				'singular_name' => 'Exit Poll',
				'add_new' => 'Add New',
				'add_new_item' => 'Add New Exit Poll',
				'edit' => 'Edit',
				'edit_item' => 'Edit Exit Poll',
				'new_item' => 'New Exit Poll',
				'view_item' => 'View Exit Poll',
				'search_items' => 'Search Exit Polls',
				'not_found' =>  'Nothing found in the Database.',
				'not_found_in_trash' => 'Nothing found in Trash',
				'parent_item_colon' => ''
			), /* end of arrays */
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_nav_menus' => false,
			'menu_position' => 8,
			//'menu_icon' => get_stylesheet_directory_uri() . '/library/images/custom-post-icon.png',
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => array( 'title', 'revisions'),
			'has_archive' => false,
			'rewrite' => false,
			'query_var' => true
		)
	);
});


/**
 * Modify queries on specific templates
 */
// add_action('pre_get_posts', function($query) {
// 	if ($query->is_post_type_archive('resource')) {
// 		$query->set('posts_per_page', -1);
// 		$query->set('orderby', 'title');
// 		$query->set('order', 'ASC');
// 	}
// 	if ($query->is_tax('resource-type')) {
// 		// resource-type should query the resource CPT
// 		$query->set('post_type', 'resource');
// 		$query->set('posts_per_page', -1);
// 		$query->set('orderby', 'title');
// 		$query->set('order', 'ASC');
// 	}
// });
//
//
// /**
//  * Add columns to admin screen
//  */
// add_filter( 'manage_resource_posts_columns', function($columns) {
// 	$new_columns['cb'] = 'cb';
// 	$new_columns['title'] = 'Title';
// 	$new_columns['resource-type'] = 'Resource Type';
// 	$new_columns['date'] = 'Date';
//
// 	$columns = $new_columns;
// 	return $columns;
// }, 10, 1);
//
// add_filter( 'manage_resource_posts_custom_column', function($column_name, $id) {
// 	if ( 'resource-type' == $column_name ) {
// 		echo get_the_term_list($id, 'resource-type', '', ', ', '');
// 	}
// }, 10, 2);
