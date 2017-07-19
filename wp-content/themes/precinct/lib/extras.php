<?php

namespace Roots\Sage\Extras;

use Roots\Sage\Setup;

/**
 * Add <body> classes
 */
function body_class($classes) {
  // Add page slug if it doesn't exist
  if (is_single() || is_page() && !is_front_page()) {
    if (!in_array(basename(get_permalink()), $classes)) {
      $classes[] = basename(get_permalink());
    }
  }

  // Add class if sidebar is active
  if (Setup\display_sidebar()) {
    $classes[] = 'sidebar-primary';
  }

  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\body_class');

/**
 * Clean up the_excerpt()
 */
function excerpt_more() {
  return ' &hellip; <a href="' . get_permalink() . '">' . __('Continued', 'sage') . '</a>';
}
add_filter('excerpt_more', __NAMESPACE__ . '\\excerpt_more');

/**
 * Modify TinyMCE editor to remove unused items
 */
add_filter('tiny_mce_before_init', function($init) {
  // Block format elements to show in dropdown
  $init['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6;';
	return $init;
});

/**
 * Prevent non-admins from accessing wp-admin
 */
add_action( 'admin_init', function() {
  $redirect = home_url( '/' );
  if ( ! ( current_user_can( 'manage_options' ) ) )
    exit( wp_redirect( $redirect ) );
}, 100 );

/**
 * Disable admin bar for all users except admins
 */
add_action('after_setup_theme', function() {
  if ( ! ( current_user_can( 'manage_options' ) ) )
    show_admin_bar(false);
});

// Search inside multidimensional array
// Modified from this:
// https://www.sitepoint.com/community/t/best-way-to-do-array-search-on-multi-dimensional-array/16382/5
function array_find_deep($array, $search, $match = true, $keys = array()) {
  $results = [];
  foreach($array as $key => $value) {
    if (is_array($value)) {
      $sub = array_find_deep($value, $search, $match, array_merge($keys, array($key)));
      if (count($sub)) {
        $results[] = $sub;
      }
    } elseif (($value === $search && $match === true) || (stristr($value, $search) && $match === false)) {
      $results = array_merge($keys, array($key));
    }
  }

  return $results;
}


function array_kshift(&$arr) {
  list($k) = array_keys($arr);
  $r  = array($k=>$arr[$k]);
  unset($arr[$k]);
  return $r;
}
