<?php

use Roots\Sage\Setup;

get_template_part('templates/components/title', get_post_type());
?>

<div class="container">
  <div class="content">
    <main class="main">
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
    <?php if (Setup\display_sidebar()) : ?>
      <aside class="sidebar">
        <?php get_template_part('templates/components/sidebar', get_post_type()); ?>
      </aside>
    <?php endif; ?>
  </div><!-- /.content -->
</div><!-- /.container -->

<?php if ($wp_query->max_num_pages > 1) : ?>
  <nav class="post-nav container">
    <?php if (function_exists('wp_pagenavi')) {
      wp_pagenavi();
    } ?>
  </nav>
<?php endif; ?>
