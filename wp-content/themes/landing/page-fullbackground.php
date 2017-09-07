<?php
/**
 * Template Name: Full Background Page
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */
 
use Roots\Sage\Setup;

$image_id = get_post_thumbnail_id();
$featured_image_lg = wp_get_attachment_image_src($image_id, 'large');

//get_template_part('templates/components/title', get_post_type());
?>
<style>
	.global-footer{
		margin-top: 0em;
	}
</style>
<div class="full_background" style="background-image: url('<?php echo $featured_image_lg[0]; ?>');padding:60px 0px 100px; background-repeat:no-repeat; background-size:cover;">
	<div class="container">
	  <div class="content">
		<main class="main" role="main">
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
</div><!-- /.full_background -->

<?php if ($wp_query->max_num_pages > 1) : ?>
  <nav class="post-nav container">
    <?php if (function_exists('wp_pagenavi')) {
      wp_pagenavi();
    } ?>
  </nav>
<?php endif; ?>
