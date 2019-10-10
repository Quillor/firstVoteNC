<?php

use Roots\Sage\Titles;

$image_id = get_post_thumbnail_id();
$featured_image_lg = wp_get_attachment_image_src($image_id, 'large');
$election = $_GET['election-option'];

$type = $_GET['results'];

if($election == null || $election ==''){
	$election = "General Election Results"; 
	
	?>
<style>
#election-selection{
	display:none;
}
</style>
<?php

if(Titles\title() != null || Titles\title() != ''){
	$election =  Titles\title();
	
}

}else{
	$election_name = str_replace(' ', '_', $election);
	$election_name = strtolower($election_name);
}

?>


<header class="page-header photo-overlay" style="background-image: url('<?php echo $featured_image_lg[0]; ?>'); ">
  <div class="article-title-overlay">
    <div class="container">
		<p id="election-selection" class="text-center extra-padding">
			<select id="page-election-option" name="page-election-option" style="height: 45px;" >
			<?php
							$q = new WP_Query([
							  'posts_per_page' => -1,
							  'post_type' => 'election'
							]);
							if($q->have_posts()){
								while($q->have_posts()){
									$q->the_post(); 
									if(get_the_title() == $election){ ?>
										<option value="<?php echo get_the_title(); ?>" selected="selected"><?php echo get_the_title(); ?></option>		
									<?php
									}else{
									?>
										<option value="<?php echo get_the_title(); ?>"><?php echo get_the_title(); ?></option>													
									<?php
									}
								}
							}
							 wp_reset_query();

			?>
				</select>	
			</label>
		</p>
      <div class="row">
        <div class="<?php if (!is_page('2016-general-election-results')) { echo 'col-md-8 col-centered'; } ?>">
          <h1 class="entry-title"><?//= Titles\title(); ?></h1>
          <h1 class="entry-title"><?php echo $election; ?></h1>

          <?php
          if (is_page('2016-general-election-results')) {
            if (isset($_GET['contest'])) {
              $race = $_GET['contest'];
              $uploads = wp_upload_dir();
              $contests = json_decode(file_get_contents($uploads['basedir'] . '/election_contests.json'), true);
              ?>
              <h2>
              <?php echo $contests[$race]['title']; ?>
              <a class="btn btn-sm btn-gray btn-small" href="<?php echo remove_query_arg('contest'); ?>">Back to all results</a>
              </h2>
              <?php if (isset($contests[$race]['question'])) { ?>
                <div class="h2"><small><?php echo $contests[$race]['question']; ?></small></div>
              <?php } ?>
            <?php } else {
             
              ?>
              <ul class="nav nav-tabs">
                <li class="partisan_nav <?php if ($type == 'partisan') echo 'active'; ?>" role="presentation" ><a href="<?php echo add_query_arg('results', 'partisan'); // remove_query_arg('results'); ?>">Partisan Contest Results</a></li>
                <li class="non_partisan_nav <?php if ($type == 'nonpartisan') echo 'active'; ?>" role="presentation"><a href="<?php echo add_query_arg('results', 'nonpartisan'); ?>">Nonpartisan Contest Results</a></li>
                <li class="issued_nav <?php if ($type == 'issues') echo 'active'; ?>" role="presentation" ><a href="<?php echo add_query_arg('results', 'issues'); ?>">Issue-Based Question Results</a></li>
                <li class="poll_nav <?php if ($type == 'participation') echo 'active'; ?>" role="presentation" ><a href="<?php echo add_query_arg('results', 'participation'); ?>">Exit Poll Data</a></li>
                <li class="precinct_nav <?php if ($type == 'precincts') echo 'active'; ?>" role="presentation" ><a href="<?php echo add_query_arg('results', 'precincts'); ?>">Precinct Results</a></li>
              </ul>
            <?php }
          } elseif (is_page('general-election-results')) {
            if (isset($_GET['contest'])) {
              $race = $_GET['contest'];
              $uploads = wp_upload_dir();
              $contests = json_decode(file_get_contents($uploads['basedir'] . '/elections/election_contests_'.$election_name.'.json'), true);
              ?>
              <h2>
              <?php echo $contests[$race]['title']; ?>
              <a class="btn btn-sm btn-gray btn-small" href="<?php echo remove_query_arg('contest'); ?>">Back to all results</a>
              </h2>
              <?php if (isset($contests[$race]['question'])) { ?>
                <div class="h2"><small><?php echo $contests[$race]['question']; ?></small></div>
              <?php } 
			}
			else {
				
				 if ($_GET['election-option'] == '2018 General Election' ) { ?>
					
				  <ul class="nav nav-tabs">
					<li class="partisan_nav <?php if ($type == 'partisan') echo 'active'; ?>" role="presentation" ><a href="<?php echo add_query_arg('results', 'partisan');  ?>">Partisan Contest Results</a></li>
					<li class="non_partisan_nav <?php if ($type == 'nonpartisan') echo 'active'; ?>" role="presentation"><a href="<?php echo add_query_arg('results', 'nonpartisan'); ?>">Judicial and Nonpartisan Contest Results</a></li>
					<li class="issued_nav <?php if ($type == 'issues') echo 'active'; ?>" role="presentation" ><a href="<?php echo add_query_arg('results', 'issues'); ?>">Referenda and Issue-Based Question Results</a></li>
					<li class="poll_nav <?php if ($type == 'participation') echo 'active'; ?>" role="presentation" ><a href="<?php echo add_query_arg('results', 'participation'); ?>">Exit Poll Data</a></li>
					<li class="precinct_nav <?php if ($type == 'precincts') echo 'active'; ?>" role="presentation" ><a href="<?php echo add_query_arg('results', 'precincts'); ?>">School Precint Results</a></li>
				  </ul>
					
				<?php	
				 }
				 else{
				?>
				
				  <ul class="nav nav-tabs">
					<li class="partisan_nav <?php if ($type == 'partisan') echo 'active'; ?>" role="presentation" ><a href="<?php echo add_query_arg('results', 'partisan'); // remove_query_arg('results'); ?>">Partisan Contest Results</a></li>
					<li class="non_partisan_nav <?php if ($type == 'nonpartisan') echo 'active'; ?>" role="presentation"><a href="<?php echo add_query_arg('results', 'nonpartisan'); ?>">Nonpartisan Contest Results</a></li>
					<li class="issued_nav <?php if ($type == 'issues') echo 'active'; ?>" role="presentation" ><a href="<?php echo add_query_arg('results', 'issues'); ?>">Issue-Based Question Results</a></li>
					<li class="poll_nav <?php if ($type == 'participation') echo 'active'; ?>" role="presentation" ><a href="<?php echo add_query_arg('results', 'participation'); ?>">Exit Poll Data</a></li>
					<li class="precinct_nav <?php if ($type == 'precincts') echo 'active'; ?>" role="presentation" ><a href="<?php echo add_query_arg('results', 'precincts'); ?>">Precinct Results</a></li>
				  </ul>
			  
            <?php
				}
			}
          } ?>
        </div>
      </div>
    </div>
  </div>
  <script>
	jQuery('#page-election-option').on('change', function() {
		var url      = window.location.href;  
		var parts = url.split('=');
		var part = parts[0];
		  //alert( part + "=" + this.value );
		  window.location.href = part + "=" + this.value + "&results=partisan";
		})
  </script>
</header>