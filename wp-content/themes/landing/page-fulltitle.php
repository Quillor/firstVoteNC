<?php
/**
 * Template Name: Title Page
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */

use Roots\Sage\Setup;

//get_template_part('templates/components/title', get_post_type());
?>
<div class="fluid-container">
  <div class="content">
    <main class="" role="main">
      <?php
      if (!have_posts()) : ?>
        <div class="alert alert-warning">
          This is not the page you're looking for.
        </div>
        <?php get_search_form();
      endif;

      while (have_posts()) : the_post();
        if (is_archive()) {
          get_template_part('templates/layouts/block', 'post-side');
        } else {
          get_template_part('templates/layouts/content', get_post_type());
        }
      endwhile;
      ?>
    </main>
  </div><!-- /.content -->
</div><!-- /.container -->
