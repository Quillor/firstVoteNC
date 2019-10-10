<?php

$uploads = network_site_url('wp-content/uploads');

$election = $_GET['election-option'];	
$election_name = str_replace(' ', '_', $election);
$election_name = strtolower($election_name);

$results = json_decode(file_get_contents($uploads . '/elections/election_results_'.$election_name.'.json'), true);
$blog_ids = array_column($results, 'blog_id');
$blog_ids_unique = array_unique($blog_ids);

// Get all sites
$sites = array();
foreach ($blog_ids_unique as $blog_id) {
  $details = get_blog_details($blog_id);
  switch_to_blog($blog_id);
    $q = new WP_Query(['posts_per_page' => 1, 'post_type' => 'election', 'post_title_like' => get_the_title()]);
    if($q->have_posts()): while($q->have_posts()): $q->the_post();
      if ($details->blogname !== 'North Carolina') {
        $sanitized = sanitize_title($details->blogname);
        $sites[$sanitized] = array(
          'name' => $details->blogname,
          'link' => get_the_permalink(),
          'count' => count(array_keys($blog_ids, $blog_id))
        );
      }
    endwhile; endif; wp_reset_postdata();
  restore_current_blog();
}

// Sort alphabetically
ksort($sites);
?>


<div class="table-responsive panel">
  <div class="panel-heading"><h2 class="h3">Explore results by precinct</h2></div>
  <div class="panel-body">
    <table class="table sortable">
      <thead>
        <tr>
          <th scope="col" id="sort-init">Precinct</th>
          <th scope="col">Votes</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($sites as $site) { 
      $link = explode("?",$site['link']);
      $election_replace = str_replace(' ','-',$election ); 
      $combine = $link[0].'?results=local&election-option='.$election;
    ?>
          <tr>
            <td><a href="<?php echo $combine; ?>" target="_blank" class="btn btn-default"><?php echo $site['name']; ?> ⟶</a></td>
            <td><?php echo $site['count']; ?> </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<div class="panel">
  <div class="panel-heading"><h2 class="h3">Map of participating schools</h2></div>
  <div class="panel-body">
    <div class="entry-content-asset">
      <?php
	
		  switch_to_blog(1);
			$q = new WP_Query(['posts_per_page' => 1, 'post_type' => 'election', 'post_title_like' => $election]);
			if($q->have_posts()): while($q->have_posts()): $q->the_post();
				//print_r($q);
				echo '<iframe src="'.get_post_meta(get_the_id(), '_cmb_map_url', true).'" width="640" height="480"></iframe>';
			endwhile; endif; wp_reset_postdata();
		  restore_current_blog();
		?>
	
	
      <!-- This is the 2016 Map -->
      <!-- <iframe src="https://www.google.com/maps/d/u/0/embed?mid=1erNunewLx3L_Z4bBNmPAjXC8Pa0" width="640" height="480"></iframe> -->
      <!-- This is the 2017 Map -->
	  <!-- <iframe src="https://www.google.com/maps/d/embed?mid=1qD7CBHvvzhvc1mqda0tXxYrtyEg" width="640" height="480"></iframe> -->
      <!-- <h3>2017</h3> -->
    </div>
  </div>
</div>


