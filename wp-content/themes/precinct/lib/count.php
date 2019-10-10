<?php
add_action('wp_ajax_nopriv_do-count', 'do_count' );
add_action('wp_ajax_do-count', 'do_count');

function do_count() {
	$vc_title ;
	$vc_id;
	$vc_votes ;
	$vc_contest;	
	$data_verify = false;	
  
  $nonce = $_POST['countNonce'];
  $masterelection = $_POST['masterelection'];
  
  $election_name = str_replace(' ', '_', $masterelection);
  $election_name = strtolower($election_name);


  // check to see if the submitted nonce matches with the
  // generated nonce we created earlier
  if ( ! wp_verify_nonce( $nonce, 'count-ajax-nonce' ) )
    die( 'Busted!');


  $blog_id = get_current_blog_id();

  $elections = new WP_Query([
    'post_type' => 'election',
    'posts_per_page' => -1,
	'post_title_like' => $masterelection,
    'fields' => 'ids'
  ]);
  $election = $elections->posts;
  $election_id = $election[0];
  $precinct_contests = '';
  
  /*
  $wp_ballot = new WP_Query(['posts_per_page' => -1, 'post_type' => 'ballot',
	'meta_query'  => array(
            array(
                'key' => '_cmb_election_id',
                'value' => $election_id
            )
        )
	]);
	
    if($wp_ballot->have_posts()){
		$data_verify = true;
	} 
	wp_reset_postdata();
	*/
	
	$wp_results = new WP_Query(['posts_per_page' => 1, 'post_type' => 'votes_contest','s'  => $election_id ]);
	
    if($wp_results->have_posts()){
		while( $wp_results->have_posts() ) : $wp_results->the_post();
			$vc_title =  get_the_title();
			$vc_id =  get_the_ID();
			$vc_votes =  get_the_content();
			$vc_contest =  get_the_excerpt();
			$data_verify = true;
		endwhile;
	} 
	wp_reset_postdata();
		  
	if($data_verify == true){
			include(locate_template('/lib/fields-statewide-races.php'));
			include(locate_template('/lib/fields-exit-poll.php'));
			include(locate_template('/lib/fields-ballot-init.php'));
			
			/*			
			if ( get_option( $precinct_contests ) !== false ) {

				// The option already exists, so we just update it.
				//update_option( $precinct_contests, $new_value );
				//$precinct_contests = json_decode(get_option('precinct_contests'), true);
				// DO NOTHING
				
			} else {
				// The option hasn't been added yet. We'll add it with $autoload set to 'no'.

				json_decode(precinct_contests($ballot_data, $included_races, $custom, $issues), true);
				add_option( 'precinct_contests', '' );
			}
			*/

		  //$precinct_contests = json_decode(get_option('precinct_contests'), true);
		  include(locate_template('/lib/fields-exit-poll.php'));
		  $election_results = array();

		  $precinct_contests = precinct_contests($ballot_data, $included_races, $custom, $referenda, $issues);
		  $election_results = precinct_votes($blog_id, $election_id, $precinct_contests, $ep_fields, $election_results);

		  $uploads = wp_upload_dir();

		  file_put_contents(
			$uploads['basedir'] . '/precinct_results_'. $election_name.'.json',
			json_encode($election_results)
		  );

		  header('Content-Type: application/json');
			if($vc_contest == null){
				$votes_contest = array(
				  'ID'           => $vc_id,
				  'post_content' => json_encode(wp_slash($election_results)) ,
				  'post_excerpt' => json_encode($precinct_contests) ,
				  'post_type' => 'votes_contest'
				);
				
			}else{
				
			  $votes_contest = array(
				  'ID'           => $vc_id,
				  'post_content' => json_encode(wp_slash($election_results)) ,
				  'post_excerpt' => json_encode($precinct_contests) ,
				  'post_type' => 'votes_contest'
				);
				
			}
			
			wp_update_post( $votes_contest );
		 
		  exit;
	}else{
		//update_option('precinct_contests', "{}");
		 // Set post_data for saving new post for VOTES and CONTEST DATA
		 $votes_contest = array(
			'post_author' => 1, // Admin
			'post_status' => 'publish',
			'post_type'   => 'votes_contest',
			'post_title'  => $election_id
		  );
		  // Create the new post for votes and contest
		 wp_insert_post( $votes_contest);
		
		exit;
	}
}

// Function to count votes
function count_votes($election_id, $contest_title, $option) {
  $cast_ballots = new WP_Query([
    'post_type' => 'ballot',
    'posts_per_page' => -1,
    'meta_query' => [
      [
        'key' => $contest_title,
        'value' => $option
      ],
      [
        'key' => '_cmb_election_id',
        'value' => $election_id
      ]
    ],
    'fields' => 'ids'
  ]);

  return $cast_ballots->posts;
}

// Count how many pollees responded a certain way
function count_pollees($election_id, $ep_question, $option, $ballot_id = NULL) {
  $ballot_match = array();

  if (!empty($ballot_id)) {
    $ballot_match = [
      'key' => '_cmb_ballot_id',
      'value' => $ballot_id,
      'compare' => 'IN'
    ];
  }

  $exit_polls = new WP_Query([
    'post_type' => 'exit-poll',
    'posts_per_page' => -1,
    'meta_query' => [
      [
        [
          'key' => $ep_question,
          'value' => $option
        ],
        [
          'key' => '_cmb_election_id',
          'value' => $election_id
        ]
      ],
      $ballot_match
    ],
    'fields' => 'ids'
  ]);

  return $exit_polls->posts;
}



/*
/**
 * Create array of all the votes, plus exit poll answers
 *
 */
function precinct_votes($blog_id, $election_id, $precinct_contests, $ep_fields, $election_results) {
  // Headers for all contests
  foreach ($precinct_contests as $s_key => $section) {
    foreach ($section as $contest) {
      $columns_contests[] = $contest['sanitized_title'];
    }
  }

  // Headers for exit polls + participation numbers by exit poll
  foreach ($ep_fields as $ep_field) {
    $columns_eps[] = $ep_field['id'];
  }

  // Create final column headers
  //$columns = array_merge(['blog_id'], $columns_contests, $columns_eps);
  // $precinct_votes[] = $columns;

  // Make rows for each vote
  $ballots = new WP_Query([
    'post_type' => 'ballot',
    'posts_per_page' => -1,
	'meta_query' => [
        [
          'key' => '_cmb_election_id',
          'value' => $election_id
        ]
      ]
  ]);
  
  //echo $election_id;
  
  if ($ballots->have_posts()) : while ($ballots->have_posts()) : $ballots->the_post();
    $ballot_id = get_the_id();
	
    // Get ballot results
    $ballot_responses = get_post_custom();
    $row_votes = array('blog_id' => $blog_id);
    foreach ($columns_contests as $contest) {
      if (isset($ballot_responses[$contest])) {
        $row_votes[$contest] = str_replace(['&lt;br /&gt;', '(', ')', ', Jr'], [' & ', '"', '"', ' Jr'], $ballot_responses[$contest][0]);
        //$row_votes[$contest] = str_replace(['&lt;br /&gt;', '(', ')', ', Jr'], [' & ', '(', ')', ' Jr'], $ballot_responses[$contest][0]);
      } else {
        $row_votes[$contest] = NULL;
      }
    }

	
	
    // Get exit poll result for this voter
    $exit_poll = new WP_Query([
      'post_type' => 'exit-poll',
      'posts_per_page' => 1,
      'meta_query' => [
        [
          'key' => '_cmb_ballot_id',
          'value' => $ballot_id
        ]
      ],
      'fields' => 'ids'
    ]);

    $pollee = $exit_poll->posts;

    if (isset($pollee[0])) {
      $ep_responses = get_post_custom($pollee[0]);

      foreach ($columns_eps as $ep) {
        $row_votes[$ep] = $ep_responses[$ep][0];
        $row_votes_state[$ep] = $ep_responses[$ep][0];
      }
    }

    $precinct_votes[] = $row_votes;
    $election_results[] = $row_votes_state;
  endwhile; endif; wp_reset_postdata();

   update_option('precinct_votes', json_encode($precinct_votes));

  return $precinct_votes;
}


/**
 * Create array of all contests on ballot
 * Loop through contests, custom contets, issue-based questions
 *
 */
 
function precinct_contests($ballot_data, $included_races, $custom, $referenda, $issues) {
  $precinct_contests = array();

  // Loop through contests
  foreach ($ballot_data as $ballot_section) {
    foreach ($ballot_section->races as $key => $race) {

      $key = array_search($race->ballot_title, $included_races);
      if ($key !== FALSE && $race->votes_allowed > 0) {
        if (!empty($race->seat)) {
          $sanitized_title = sanitize_title($race->ballot_title . '-' . $race->seat);
        } else {
          $sanitized_title = sanitize_title($race->ballot_title);
        }

        $precinct_contests[$ballot_section->section]['_cmb_ballot_' . $sanitized_title] = [
          'title' => $race->ballot_title,
          'district' => $race->district,
          'sanitized_title' => '_cmb_ballot_' . $sanitized_title,
          'number' => $race->votes_allowed
        ];

        foreach ($race->candidates as $can) {
          if ($ballot_section->section == 'Partisan Offices') {
            $details = [
              //'name' => str_replace(['"', '<br />', '(', ')', ', Jr'], [ "\'", ' & ', '\(', '\)', ' Jr'], $can->ballotName),
              'name' => str_replace(['"', '<br />', '(', ')', ', Jr'], [ "\'", ' & ', '"', '"', ' Jr'], $can->ballotName),
              'party' => str_replace(['"', ' Party', 'Democratic'], ["\'", '', 'Democrat'], $can->party)
            ];
          } else {
            $details = [
              //'name' => str_replace(['"', '<br />', '(', ')', ', Jr'], ["\'",' & ', '\(', '\)', ' Jr'], $can->ballotName)
              'name' => str_replace(['"', '<br />', '(', ')', ', Jr'], ["\'",' & ', '\"', '\"', ' Jr'], $can->ballotName)
            ];
          }

          $precinct_contests[$ballot_section->section]['_cmb_ballot_' . $sanitized_title]['candidates'][] = $details;
        }
      }
    }
  }

  // Loop through custom contests
  foreach ($custom as $contest) {
    if (!empty($contest['title'])) {
      $sanitized_title = '_cmb_ballot_' . sanitize_title($contest['title']);
      $precinct_contests[$contest['section']][$sanitized_title] = [
        'title' => $contest['title'],
        'sanitized_title' => $sanitized_title,
        'number' => $contest['votes_allowed']
      ];

      $candidates = explode("\n", str_replace("\r", "", $contest['candidates']));
      foreach ($candidates as $c_key => $candidate) {
        // Get party
        preg_match('/\(([A-Za-z0-9 ]+?)\)/', $candidate, $party);

        if (!empty($party[0])) {
          $precinct_contests[$contest['section']][$sanitized_title]['candidates'][$c_key]['party'] = $party[1];

          $candidate = str_replace($party[0], '', $candidate);
          $candidate = str_replace(['"',', Jr'], ['',' Jr'], $candidate);
        }

        $precinct_contests[$contest['section']][$sanitized_title]['candidates'][$c_key]['name'] = $candidate;
      }
    }
  }
  
    // Loop through referenda
  $k = 0;
  foreach ($referenda as $question) {
    if (!empty($question)) {
      $sanitized_title = '_cmb_ballot_' . sanitize_title($question['title']) . '-' . $k;
      $precinct_contests['Issues'][$sanitized_title] = [
        'title' => $question['title'],
        'sanitized_title' => $sanitized_title,
        'question' =>  str_replace(['"', '(', ')',], ["'", '\"', '\"',], $question['question']),
      ]; 

      if (empty($question['options'])) {
        $precinct_contests['Issues'][$sanitized_title]['options'] = ['For', 'Against'];
      } else {
        $precinct_contests['Issues'][$sanitized_title]['options'] = $question['options'];
      }
      $k++;
    }
  }

  // Loop through issue-based questions
  $k = 0;
  foreach ($issues as $question) {
    if (!empty($question)) {
      $sanitized_title = '_cmb_ballot_' . sanitize_title($question['title']) . '-' . $k;
      $precinct_contests['Issues'][$sanitized_title] = [
        'title' => $question['title'],
        'sanitized_title' => $sanitized_title,
        'question' =>  str_replace(['"', '(', ')',], ["'", '\"', '\"',], $question['question']),
      ]; 

      if (empty($question['options'])) {
        $precinct_contests['Issues'][$sanitized_title]['options'] = ['Yes', 'No'];
      } else {
        $precinct_contests['Issues'][$sanitized_title]['options'] = $question['options'];
      }
      $k++;
    }
  }
  

  
  wp_cache_delete ( 'alloptions', 'options' );
  
  update_option('precinct_contests', json_encode($precinct_contests));

  return $precinct_contests;
}
add_filter( 'posts_where', 'title_like_posts_where', 10, 2 );
function title_like_posts_where( $where, &$wp_query ) {
    global $wpdb;
    if ( $post_title_like = $wp_query->get( 'post_title_like' ) ) {
        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'' . esc_sql( $wpdb->esc_like( $post_title_like ) ) . '%\'';
    }
    return $where;
}