<?php

use Roots\Sage\Setup;
use Roots\Sage\Titles;



get_template_part('templates/components/title', get_post_type());
?>

<div class="container">

	

  <?php
	$election = $_GET['election-option'];
	
	$election_name = str_replace(' ', '_', $election);
	$election_name = strtolower($election_name);
	
	//echo strtolower($election);
	
	//$election = str_replace(' ', '_', $election);
	
	//echo strtolower($election);
  
    if (isset($_GET['contest'])) {
      get_template_part('templates/layouts/results', 'contest');
    } elseif ($_GET['results'] == 'precincts') {
      get_template_part('templates/layouts/results', 'precincts');
    } elseif ($_GET['results'] == 'participation') {
      get_template_part('templates/layouts/results', 'participation');
    } elseif($election == null) {?>
		<style>
			.container .nav.nav-tabs{
				display:none;
			}
		</style>
	<form method="GET" action="">
		<p class="text-center extra-padding">
		  <label style="display:initial;"> Select Election to be view.<br/>
			<select id="election-option" name="election-option" style="height: 45px;" >
			<option>Select</option>
			<?php
							$q = new WP_Query([
							  'posts_per_page' => -1,
							  'post_type' => 'election'
							]);
							if($q->have_posts()){
								while($q->have_posts()){
									$q->the_post(); ?>
										<option value="<?php echo get_the_title(); ?>"><?php echo get_the_title(); ?></option>													
									<?php
								}
							}
							 wp_reset_query();

			?>
				</select>	
			</label>
			<input type="hidden" name="" value="">
			<input type="submit" class="btn btn-primary btn-lg" />
		</p>
	</form>

<?php	
	}else{
      get_template_part('templates/layouts/results');
	}
  ?>
</div><!-- /.container -->
