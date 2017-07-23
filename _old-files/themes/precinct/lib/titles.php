<?php

namespace Roots\Sage\Titles;

/**
 * Page titles
 */
function title() {
  if (is_home()) {
    if (get_option('page_for_posts', true)) {
      return get_the_title(get_option('page_for_posts', true));
    } else {
      return __('Teacher Portal', 'sage');
    }
  } elseif (is_archive()) {
    return get_the_archive_title();
  } elseif (is_search()) {
    return sprintf(__('Search Results for %s', 'sage'), get_search_query());
  } elseif (is_404()) {
    return __('Not Found', 'sage');
  } elseif (is_singular('election') && !isset($_GET['results'])) {
    return __('Edit Election', 'sage');
  } else {
    return get_the_title();
  }
}

/**
 * Remove prefixes from some titles
 */
add_filter( 'get_the_archive_title', function ($title) {
  if ( is_category() ) {
     $title = single_cat_title( '', false );
  }
  if ( is_tax() ) {
    $title = single_term_title( '', false );
  }
  if ( is_author() ) {
    $title = get_the_author();
  }
  return $title;
});
