<section class="precinct-admin">
  <div class="container">
    <div class="row extra-bottom-margin">
      <div class="col-md-6 border-right-gray">

        <?php
		$ctr=1;
		$current=0;
		$current_end=0;
		
        $election = new WP_Query([
          'post_type' => 'election',
          'orderby' => 'date',
		  'order'   => 'DESC',
          'posts_per_page' => -1
        ]);
		$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$actual_link =str_replace('?manage','lesson-plans/', $actual_link );
		
        ?>
		
		<div class="alert-scarlet pt-2 text-center img-rounded hidden">
			<h2 class="text-center">Coming Soon</h2>
			<p>The current simulation election will be available in late October. Until then how about viewing lessons plans for this year?</p>
			 <a class="btn btn-white text-center" href="<?php echo $actual_link;?>">View Lessons Plans</a>
		</div>
		
			<?php /*Current*/ ?>
        <table class="table table-condensed">
          <thead>
            <tr>
              <th scope="col" class="h3">Upcoming Simulation Elections</th>
            </tr>
          </thead>

          <tbody>

          <?php if ($election->have_posts()) : while ($election->have_posts()): $election->the_post();
				//echo get_the_id()
				$election_name = str_replace(' ', '_', get_the_title());
				$election_name = strtolower($election_name);
				
				  // Dates when polls are open
				  $early_voting = new DateTime();
				  $early_voting->setTimestamp(strtotime(get_post_meta(get_the_id(), '_cmb_early_voting', true)));
				  $early_voting->setTime(00, 00, 00);

				  $voting_start = $early_voting->getTimestamp();

				  $election_day = new DateTime();
				  $election_day->setTimestamp(strtotime(get_post_meta(get_the_id(), '_cmb_voting_day', true)));
				  $election_day->setTime(00, 00, 00); // This was set to 19,30,00 but the polls closed at 14:30, even though the polls opened on time same day. *shrug*

				  $voting_end = $election_day->getTimestamp();

				  // Temp/testing timestamp
				  // $today = new DateTime();
				  // $today->setDate(2016, 10, 25);
				  // $today->setTime(9, 00, 00);
				  // $now = $today->getTimestamp();

				  // Now timestamp
				  $now = current_time('timestamp');
				  $today = new DateTime();
				  $today->setTimestamp($now);
				  $today->setTimeZone(new DateTimeZone('America/New_York'));
 
				
				if ($voting_start >= $now ) {

					if ( current_user_can( 'editor' ) ) { 
						$data_url = get_the_permalink() . "?results=general&election-option=".get_the_title();
					?>
						
					  <tr>
						<th scope="row">
						  <?php the_title(); ?>
							<div class="pull-right right">
								<span class="small"><a class="btn btn-primary btn-xs" href="<?php the_permalink(); ?>?edit">Edit</a> <a class="btn btn-default btn-xs" href="<?php the_permalink(); ?>?preview">Preview Ballot</a> <a  class="btn btn-default btn-xs" href="<?php echo $data_url;?>">Results</a></span>			  
							</div>
						  <?php// echo date('m/d/Y', strtotime(get_post_meta(get_the_id(), '_cmb_early_voting', true))); ?>
						  <?php //echo date('m/d/Y', strtotime(get_post_meta(get_the_id(), '_cmb_voting_day', true))); ?>
						</th>
					  </tr>

					<?php } else { ?>

					  <tr>
						<th scope="row">
						  <?php the_title(); ?>
						  <div class="pull-right right">
							<span class="small"><a class="btn btn-default btn-xs" href="<?php echo add_query_arg('preview', '', the_permalink()); ?>">Preview Ballot</a></span> 
							<span class="small"><a class="btn btn-default btn-xs" href="<?php echo add_query_arg('results', 'general', the_permalink()); ?>">View Results</a></span>
						  </div>
						  <?php //echo date('m/d/Y', strtotime(get_post_meta(get_the_id(), '_cmb_early_voting', true))); ?> 
						  <?php //echo date('m/d/Y', strtotime(get_post_meta(get_the_id(), '_cmb_voting_day', true))); ?>
						</th>
					  </tr>

					
					<?php   } 
				}else{ 
				
					if($current_end <= 0){ ?>
							</tbody>
						</table>
					<?php  $current_end++; ?>
						<table class="table table-condensed">
							<thead>
								<tr>
									<th scope="col" class="h3">Past Simulation Elections</th>
								</tr>
							</thead>

						  <tbody>
					<?php } ?>
						
					
					
					<?php if ( current_user_can( 'editor' ) ) { 
							$data_url = get_the_permalink() . "?results=general&election-option=".get_the_title();
					?>
						
					  <tr>
						<th scope="row">
						    <?php 
								the_title();
								if ($voting_start <= $now && $now <= $voting_end) {
									echo '<small class="ongoing">Active</small>' ;
								}

							?>
							
							<div class="pull-right right">
								<span class="small"><a class="btn btn-primary btn-xs hidden" href="<?php the_permalink(); ?>?edit">Edit</a> <a class="btn btn-default btn-xs hidden" href="<?php the_permalink(); ?>?preview">Preview Ballot</a> <a  class="btn btn-default btn-xs" href="<?php echo $data_url;?>">Results</a></span>			  
							</div>
						  <?php// echo date('m/d/Y', strtotime(get_post_meta(get_the_id(), '_cmb_early_voting', true))); ?>
						  <?php //echo date('m/d/Y', strtotime(get_post_meta(get_the_id(), '_cmb_voting_day', true))); ?>
						</th>
					  </tr>

					<?php } else { ?>

					  <tr>
						<th scope="row">
						  <?php the_title(); ?>
						  <div class="pull-right right">
							<span class="small"><a class="btn btn-default btn-xs hidden" href="<?php echo add_query_arg('preview', '', the_permalink()); ?>">Preview Ballot</a></span> 
							<span class="small"><a class="btn btn-default btn-xs" href="<?php echo add_query_arg('results', 'general', the_permalink()); ?>">View Results</a></span>
						  </div>
						  <?php //echo date('m/d/Y', strtotime(get_post_meta(get_the_id(), '_cmb_early_voting', true))); ?> 
						  <?php //echo date('m/d/Y', strtotime(get_post_meta(get_the_id(), '_cmb_voting_day', true))); ?>
						</th>
					  </tr>

					
					<?php   } 
					
				}
			$ctr++;
			endwhile; else: ?>

            <tr>
              <td colspan="2">
                <div class="well well-sm">

                </div>
              </td>
            </tr>

          <?php endif; wp_reset_postdata(); ?>
		  
           
          </tbody>
        </table>
		
		
		
		 <?php
			
			if($ctr>1){ ?> 
                <div class="well well-sm">
                  <p><em>You already have a simulation election in your precinct.</em></p>

                  <?php if ( current_user_can( 'editor' ) ) { ?>
                    <a class="btn btn-default" href="?add">Add Another Simulation Election</a>
                  <?php } ?>

                </div>
			<?php   } else{
				 ?> 
                <div class="well well-sm">
                  <p><em>You don't have a simulation elections in your precinct.</em></p>

                  <?php if ( current_user_can( 'editor' ) ) { ?>
                    <a class="btn btn-default" href="?add">Add Simulation Election</a>
                  <?php } ?>

                </div>
			<?php 
			}?>

		<script type="text/javascript">
			function theFunction (url, election) {
				jQuery('#election-option').val(election);
				jQuery('#data_url').val(url);
				jQuery('#count-votes').click();
				//console.log(election);
				//console.log(url);
			}
		</script>
		
        <?php if (is_super_admin()) { ?>
          <p class="text-center extra-padding" style="">
			  <label style="display:initial;"> Select Election to count/recount.<br/>
				<select id="election-option" style="height: 45px;" >
				<option>Select</option>
				<?php
				
								$q = new WP_Query([
								  'posts_per_page' => -1,
								  'post_type' => 'election'
								]);
								if($q->have_posts()){
									while($q->have_posts()){
										$q->the_post(); ?>
										<?php
											$election_day = new DateTime();
											$election_day->setTimestamp(strtotime(get_post_meta(get_the_id(), '_cmb_voting_day', true)));
											$election_day->setTime(00, 00, 00); // This was set to 19,30,00 but the polls closed at 14:30, even though the polls opened on time same day. *shrug*
											$voting_end = $election_day->getTimestamp();
											if($voting_end  > strtotime('-7 days')) {
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
			<input type="hidden" name="data_url" id="data_url"/>
            <button type="button" class="btn btn-primary btn-lg" id="count-votes" data-toggle="modal" data-target="#tally-modal" data-backdrop="static" data-keyboard="false">Count Votes!</button>
          </p>

          <div class="modal fade" id="tally-modal" tabindex="-1" role="dialog" style="visibility:hidden;opacity:0;">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">Hang tight! We're counting votes at each precinct:</h4>
                </div>
                <div class="modal-body">
                  <div id="script-progress"></div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" id="btn-close" style="display: none;" data-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>
        <?php } ?>
      </div>

      <div class="col-md-6 " >

        <?php
        // Only show for school-specific precincts
        if (get_bloginfo() !== 'North Carolina') :
          $officials = get_users();
          ?>
			
			
          <table class="table table-hover table-condensed">
            <thead>
              <tr>
                <th scope="col" class="h3">Election Officials</th>
                <th scope="col">Title</th>
                <th scope="col">Class</th>
                <th scope="col">&nbsp;</th>
              </tr>
            </thead>

            <tbody>

              <?php foreach ($officials as $official) : if ($official->ID != 1) : ?>

                <tr >
                  <th scope="row" style="vertical-align: middle;">
                      <?php echo $official->display_name; ?>
                  </th>
                  <td style="vertical-align: middle;">
					  <?php if (user_can($official, 'edit_pages')) { ?>
						Precinct Director
                    <?php } else{ echo "Teacher";}?>
				  </td>
                  <td style="vertical-align: middle;"><?php echo get_user_meta($official->ID, 'classes', true); ?></td>
                  <td style="vertical-align: middle;">
					<a class="btn btn-default" href="mailto:<?php echo $official->user_email; ?>">
                      <?php echo "Send Email";?>
					</a>
				  </td>
                </tr>

              <?php endif; endforeach; ?>

            </tbody>
          </table>

        <?php endif; ?>

      </div>
    </div>

    <div class="row hidden">
      <div class="col-md-6">
        <h3>TurboVote for Teachers</h3>
        <div class="entry-content-asset" style="height: 500px;"><iframe src="https://firstvotenc.turbovote.org"></iframe></div>
        <p class="small">Powered by TurboVote: <a href="https://firstvotenc.turbovote.org">register to vote, request absentee ballots, and get election reminders</a></p>
      </div>

      <div class="col-md-6">
        <h3>Informational Webinar</h3>
        <div class="entry-content-asset"><iframe width="560" height="315" src="https://www.youtube.com/embed/_ZYJYFWe8Dg" frameborder="0" allowfullscreen></iframe></div>
        <p>This webinar provides an overview of the First Vote North Carolina project, including implementation ideas, training on customizing your school's online ballot, instruction on utilizing the exit poll data for post-election analysis, and a summary of the adaptable curricular resources.</p>
        <p><strong>Questions?</strong> We're here to help: <a href="mailto:help@firstvotenc.org">help@firstvotenc.org</a></p>
      </div>
    </div>

  </div>
</section>
