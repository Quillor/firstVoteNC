<table class="table table-responsive sortable">
  <thead>
    <tr>
      <th scope="col">ID</th>
      <th scope="col">Precinct</th>
      <th scope="col">School</th>
      <th scope="col">Election</th>
      <th scope="col">Created</th>
      <th scope="col">Votes</th>
      <th scope="col">Teachers</th>
    </tr>
  </thead>
  <tbody>
  
 <?php
	 
	require_once("../../../wp-load.php");
	
		
    if(isset($_POST['election'])){
		$election = $_POST['election'];
    }
    $i = 1;
    if(is_multisite()){
        global $wpdb;
        $blogs = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->blogs WHERE spam = '%d' AND deleted = '%d' and archived = '%d' and public='%d'", 0, 0, 0, 0));
        if(!empty($blogs)){
            ?><?php
            foreach($blogs as $blog){
                switch_to_blog($blog->blog_id);
                $details = get_blog_details($blog->blog_id);
                $q = new WP_Query([
                  'posts_per_page' => -1,
                  'post_type' => 'election',
				  'post_title_like' => $election
                ]);

				
                if($q->have_posts()){
                    while($q->have_posts()){
						
						//print_r($q);
						
						
                        $q->the_post();
						
						//echo get_the_ID();
						//exit;
                        ?>
                        <tr>
                          <td><?php echo $i; ?></td>
                          <td><a href="<?php echo get_site_url(); ?>" target="_blank"><?php echo $details->path; ?></a></td>
                          <td><?php echo $details->blogname; ?></td>
                          <td><a href="<?php echo get_the_permalink(); ?>" target="_blank"><?php echo get_the_title(); ?></a></td>
                          <td>
                            <?php
                            $the_time = new DateTime();
                            $the_time->setTimestamp(get_the_time('U'));
                            $the_time->setTimeZone(new DateTimeZone('America/New_York'));
                            echo $the_time->format('m/d/Y') . '<br />' . $the_time->format('g:ia T');
                            ?>
                          </td>
                          <td>
                            <?php
                            $n = new WP_Query([
                              'post_type' => 'ballot',
                              'posts_per_page' => -1,
							  'meta_query' => [
								[
								  'key' => '_cmb_election_id',
								  'value' => get_the_ID()
								]
							  ]
                            ]);
                            echo $n->found_posts;
                            ?>
                          </td>
                          <td>
                            <?php
                            $u = get_users([$blog->blog_id]);
                            foreach ($u as $user) {
                              echo $user->user_email . '<br />';
                            }
                            ?>
                          </td>
                        </tr>
                        <?php
                        $i++;
                    }
                }
                wp_reset_query();
                restore_current_blog();
            }
        }
    }
    ?>
  </tbody>
</table>