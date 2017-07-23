<?php

use Roots\Sage\Wrapper;

$class = '';
if ( isset($_GET['post_submitted']) ) {
  $class = 'exit-poll';
} elseif ( isset($_GET['results']) ) {
  $class = 'results students';
} elseif ( isset($_GET['edit']) || isset($_GET['add']) || isset($_GET['manage'])) {
  $class = 'edit-election';
} elseif ( is_singular('election') ) {
  $class = 'ballot';
} else {
  $class = 'students';
}
?>

<!doctype html>
<html <?php language_attributes(); ?>>
  <?php get_template_part('templates/layouts/head'); ?>
  <body <?php body_class($class); ?>>
    <a class="sr-only sr-only-focusable" href="#document">Skip to main content</a>
    <!--[if IE]>
      <div class="alert alert-warning">
        <?php _e('You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.', 'sage'); ?>
      </div>
    <![endif]-->
    <?php
      do_action('get_header');
      get_template_part('templates/layouts/header');
    ?>
    <div class="wrap clearfix" id="document" role="document">
      <?php include Wrapper\template_path(); ?>
    </div><!-- /.wrap -->
    <?php
      do_action('get_footer');
      get_template_part('templates/layouts/footer');
      wp_footer();
    ?>
  </body>
</html>
