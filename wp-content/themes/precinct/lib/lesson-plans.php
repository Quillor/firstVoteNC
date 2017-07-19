<?php

namespace Roots\Sage\LessonPlans;

/**
 * Create new page called 'lesson-plans'
 *
 */
add_filter('init', function() {
  $query = new \WP_Query( ['pagename' => 'lesson-plans'] );
  if ( ! $query->have_posts() ) {
    wp_insert_post(
      array(
        'post_content'   => '[lesson-plans]',
        'post_name'      => 'lesson-plans',
        'post_title'     => 'Lesson Plans',
        'post_status'    => 'publish',
        'post_type'      => 'page',
        'ping_status'    => 'closed',
        'comment_status' => 'closed',
      )
    );
  }
});


/**
 * Add new shortcode that displays the lesson-plan content from main site
 *
 */
add_shortcode( 'lesson-plans', function($attributes, $content = null) {
  switch_to_blog(1);
    $query = new \WP_Query( ['post_type' => 'page', 'pagename' => 'lesson-plans'] );
    if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post();
      $results = get_the_content();
    endwhile; else:
      $results = '';
    endif; wp_reset_postdata();
  restore_current_blog();

  return $results;
});
