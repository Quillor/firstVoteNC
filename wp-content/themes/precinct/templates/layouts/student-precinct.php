<section class="precinct">
  <div class="container">
    <div class="row extra-bottom-margin extra-top-margin">
      <div class="col-md-7 col-centered">
        <?php
		$vote_off= true;
        // Get upcoming election
        $election = new WP_Query([
          'posts_per_page' => 5,  // Just get most recent 5 post. But in the future we'll need to change the election dates to be stored with timestamps so we can query them
          'post_type' => 'election',
		  'order' => 'DESC' // latest
        ]);

        if ($election->have_posts()) : while ($election->have_posts()) : $election->the_post();

          // After election, just show results
          //wp_redirect( add_query_arg('results', 'general', get_the_permalink()) );
          //exit;

          /**
           * When election is live
           *
           *
           *
           */
		   

          /// Get dates
          $early_voting = strtotime(get_post_meta(get_the_id(), '_cmb_early_voting', true));
          $election_day = strtotime(get_post_meta(get_the_id(), '_cmb_voting_day', true));

          // Dates when polls are open
          $early_voting = new DateTime();
          $early_voting->setTimestamp(strtotime(get_post_meta(get_the_id(), '_cmb_early_voting', true)));
          $early_voting->setTime(07, 00, 00);

          $voting_start = $early_voting->getTimestamp();

          $election_day = new DateTime();
          $election_day->setTimestamp(strtotime(get_post_meta(get_the_id(), '_cmb_voting_day', true)));
          $election_day->setTime(21, 00, 00); // This was set to 19,30,00 but the polls closed at 14:30, even though the polls opened on time same day. *shrug*

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
 
          // // Check if today is during voting period
          if ($voting_start <= $now && $now <= $voting_end) {
            // Is it between 7:30am and 7:30pm?
			
            $open = clone $today;
            $open->setTime(07, 00, 00);
            $close = clone $today;
            $close->setTime(21, 00, 00);

            if ($open->getTimestamp() <= $now && $now <= $close->getTimestamp()) {
              $canvote = true;
            } else {
              $canvote = false;
            }
          } else {
            $canvote = false;
          }
		  
			if ($canvote === false) { 
			//do nothing
            } else{
				$vote_off = false;
          ?>
			<style>
				.precinct{
					background: #000000 url("<?php echo network_site_url().'wp-content/themes/precinct/img/fireworks.jpg';?>");
					background-repeat:no-repeat;
					background-size:cover;
					min-height: 800px;
				}
				.page-header{
					margin-bottom:0px;
				}
			</style>
          <h2><?php the_title(); ?></h2>
          <p>
            <strong>Early voting:</strong>
            <?php echo date('F j, Y', $voting_start); ?> -
            <?php //echo date('F j, Y', strtotime('0 day', $voting_end)); ?>
            <?php echo date('F j, Y', strtotime('-1 day', $voting_end)); ?>
          </p>
          <p><strong>Election day:</strong> 
			<?php 
			//echo date('F j, Y', strtotime('-1 day', $voting_end));
			echo date('F j, Y', strtotime('0 day', $voting_end));
			?>
		  </p>
          <p><strong>Poll hours:</strong> 7:00am - 4:00pm</p>

          <p><a class="btn btn-default" href="<?php the_permalink(); ?>"> Vote now!
            <?php/*
            if ($canvote === false) {
              echo 'View sample ballot';
            } else {
              echo 'Vote now!';
            } */?>
          </a></p>
          <?php
			}
        endwhile; endif; wp_reset_postdata();
				if($vote_off==true){
					?>             
					<h2 class="text-center">Voting is still closed at this hour.</h2>
			<?php }
		
        ?>
      </div>
    </div>
  </div>
</section>
