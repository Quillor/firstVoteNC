<?php
add_action('wp_ajax_nopriv_co-count', 'do_count' );
add_action('wp_ajax_do-count', 'do_count');

function do_count() {

  $nonce = $_POST['countNonce'];
  $masterelection = $_POST['masterelection'];
  
  $election_name = str_replace(' ', '_', $masterelection);
  $election_name = strtolower($election_name);

  // check to see if the submitted nonce matches with the
  // generated nonce we created earlier
  if ( ! wp_verify_nonce( $nonce, 'count-ajax-nonce' ) )
    die( 'Busted!');

  // Set up statewide election results array
  include(locate_template('/lib/fields-statewide-races.php'));
  $election_contests = array();
  $election_results = array();

  $uploads = wp_upload_dir();

  $i = $_POST['start'];
  $batch_size = 2;
  $max = $i + $batch_size;

  // If this is a recount, delete all statewide count data and start over
  // Otherwise, append this data to existing statewide data
  if ($i == 0) {
    file_put_contents($uploads['basedir'] . '/elections/election_contests_'.$election_name.'.json', '');
    file_put_contents($uploads['basedir'] . '/elections/election_results_'.$election_name.'.json', '');

    // write our progress file
    file_put_contents(
      $uploads['basedir'] . '/elections/count-progress_'.$election_name.'.json',
      json_encode([
        'percentComplete' => 0
      ])
    );
  }

  // Iterate through all sites in batches
  if(is_multisite()){
    global $wpdb;
    $blogs = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->blogs WHERE spam = '%d' AND deleted = '%d' and archived = '%d' and public='%d'", 0, 0, 0, 0));
    if(!empty($blogs)){

      // How many blogs do we need to go through?
      $totalItems = count($blogs);
      if ($max > $totalItems - 1) {
        $max = $totalItems - 1;
      }

      // Iterate in batches to prevent timeout
      for (; $i <= $max; $i++) {
        $blog = $blogs[$i];
        switch_to_blog($blog->blog_id);
        $details = get_blog_details($blog->blog_id);
        $q = new WP_Query([
          'posts_per_page' => -1,
          'post_type' => 'election',
		  'post_title_like' => $masterelection
        ]);
        if ($q->have_posts()) : while ($q->have_posts()) : $q->the_post();
			
          include(locate_template('/lib/fields-exit-poll.php'));
          include(locate_template('/lib/fields-ballot-init.php'));

          // Only do this for precincts where the ballot was created AND customized
          if (is_array($included_races) ) {

            $precinct_contests = precinct_contests($ballot_data, $included_races, $custom, $referenda, $issues);
            $election_results = precinct_votes($blog->blog_id, $election_id, $statewide_races, $ep_fields, $precinct_contests, $election_results);

            foreach ($precinct_contests as $pc) {
              $election_contests = array_merge($election_contests, $pc);
            }

          }

        endwhile; endif; wp_reset_postdata();
        restore_current_blog();

        // write our progress file
        file_put_contents(
          $uploads['basedir'] . '/elections/count-progress_'.$election_name.'.json',
          json_encode([
            'percentComplete' => $i/$totalItems
          ])
        );
      }
    }
  }

  // Update all contests and all results
  $saved_ec = json_decode(file_get_contents($uploads['basedir'] . '/elections/election_contests_'.$election_name.'.json'), true);
  if (is_array($saved_ec)) {
    $new_ec = array_merge($saved_ec, $election_contests);
  } else {
    $new_ec = $election_contests;
  }
  file_put_contents(
    $uploads['basedir'] . '/elections/election_contests_'.$election_name.'.json',
    json_encode($new_ec)
  );

  $saved_er = json_decode(file_get_contents($uploads['basedir'] . '/elections/election_results_'.$election_name.'.json'), true);
  if (is_array($saved_er)) {
    $new_er = array_merge(array_values($saved_er), array_values($election_results));
  } else {
    $new_er = $election_results;
  }
  file_put_contents(
    $uploads['basedir'] . '/elections/election_results_'.$election_name.'.json',
    json_encode($new_er)
  );

  // Output
  header('Content-Type: application/json');
  echo json_encode([
    'total' => $totalItems,
    'start' => $i,
    'ec' => count($new_ec),
    'er' => count($new_er)
  ]);

  exit;
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
              'name' => str_replace(['<br />', '(', ')', ', Jr'], [' & ', '"', '"', ' Jr'], $can->ballotName),
              'party' => str_replace([' Party', 'Democratic'], ['', 'Democrat'], $can->party)
            ];
          } else {
            $details = [
              'name' => str_replace(['<br />', '(', ')', ', Jr'], [' & ', '"', '"', ' Jr'], $can->ballotName)
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
        'question' => $question['question']
      ];

      if (empty($question['options'])) {
        $precinct_contests['Issues'][$sanitized_title]['options'] = ['Yes', 'No'];
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
        'question' => $question['question']
      ];

      if (empty($question['options'])) {
        $precinct_contests['Issues'][$sanitized_title]['options'] = ['Yes', 'No'];
      } else {
        $precinct_contests['Issues'][$sanitized_title]['options'] = $question['options'];
      }
      $k++;
    }
  }

  update_option('precinct_contests', json_encode($precinct_contests));

  return $precinct_contests;
}

/**
 * Create array of all the votes, plus exit poll answers
 *
 */
function precinct_votes($blog_id, $election_id, $statewide_races, $ep_fields, $precinct_contests, $election_results) {
  // Headers for all contests
  foreach ($precinct_contests as $s_key => $section) {
    foreach ($section as $contest) {
      $columns_contests[] = $contest['sanitized_title'];
    }
  }

  // Headers for exit polls + participation numbers by exit poll
  foreach ($ep_fields as $ep_field) {
    $columns_eps[] = $ep_field['id'];

    foreach ($ep_field['options'] as $ep_key => $ep_option) {
      // Participation by exit poll responses
      $pollees = count_pollees($election_id, $ep_field['id'], $ep_key);
      $participation[$ep_field['id']][$ep_key] = sizeof($pollees);
    }
  }

  // Create final column headers
  // $columns = array_merge(['blog_id'], $columns_contests, $columns_eps);
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

  if ($ballots->have_posts()) : while ($ballots->have_posts()) : $ballots->the_post();
    $ballot_id = get_the_id();

    // Get ballot results
    $ballot_responses = get_post_custom();
    $row_votes = array('blog_id' => $blog_id);
    foreach ($columns_contests as $contest) {
      if (isset($ballot_responses[$contest])) {
        $row_votes[$contest] = str_replace(['&lt;br /&gt;', '(', ')', ', Jr'], [' & ', '"', '"', ' Jr'], implode(', ', $ballot_responses[$contest]));
      } else {
        $row_votes[$contest] = NULL;
      }
    }

    // Add to statewide results array
    $row_votes_state = array('blog_id' => $blog_id);
    foreach ($statewide_races as $contest) {
      if (isset($ballot_responses[$contest])) {
        $row_votes_state[$contest] = str_replace(['&lt;br /&gt;', '(', ')', ', Jr'], [' & ', '"', '"', ' Jr'], implode(', ', $ballot_responses[$contest]));
      } else {
        $row_votes_state[$contest] = NULL;
      }
    }

    // Get exit poll result for this voter
    $pollee = count_pollees($election_id, '_cmb_ballot_id', $ballot_id);
    if (isset($pollee[0])) {
      foreach ($columns_eps as $ep) {
        $row_votes[$ep] = get_post_meta($pollee[0], $ep, true);
        $row_votes_state[$ep] = get_post_meta($pollee[0], $ep, true);
      }
    }

    $precinct_votes[] = $row_votes;
    $election_results[] = $row_votes_state;
  endwhile; endif; wp_reset_postdata();

  update_option('precinct_votes', json_encode($precinct_votes));

  return $election_results;
}


/**
 * Tabulate results:
 *
 * Results broken down by exit poll response
 *
 */
 function all_results($election_id, $ep_fields, $all_contests) {
   $results = array();
   $tabulated_results = array();

    //Separate by section
   foreach ($all_contests as $s_key => $section) {
     $columns_contests = array();

     foreach ($section as $contest) {
        //CSV column headers for contests
       $columns_contests[] = $contest['title'];

       // Tabulate results for each contest
       if (!empty($contest['candidates'])) {
          //Include title in tabulated results array
         $tabulated_results[$s_key][$contest['sanitized_title']]['title'] = $contest['title'];

         foreach ($contest['candidates'] as $c_key => $candidate) {
           $ballots = count_votes($election_id, $contest['sanitized_title'], $candidate);

            //Results without exit poll breakdown
           $tabulated_results[$s_key][$contest['sanitized_title']]['results'][$c_key] = $candidate;
           $tabulated_results[$s_key][$contest['sanitized_title']]['results'][$c_key]['votes'] = sizeof($ballots);

           // Results broken down by exit poll
           foreach ($ep_fields as $ep_field) {
             foreach ($ep_field['options'] as $ep_key => $ep_option) {
               $pollees = count_pollees($election_id, $ep_field['id'], $ep_key, $ballots);

               // Under each candidate
               $tabulated_results[$s_key][$contest['sanitized_title']]['results'][$c_key]['exit_polls'][$ep_field['id']]['title'] = $ep_field['name'];
               $tabulated_results[$s_key][$contest['sanitized_title']]['results'][$c_key]['exit_polls'][$ep_field['id']]['results'][$ep_key] = sizeof($pollees);

                //Candidates under each exit poll
               $tabulated_results[$s_key][$contest['sanitized_title']]['exit_polls'][$ep_field['id']]['title'] = $ep_field['name'];
               $tabulated_results[$s_key][$contest['sanitized_title']]['exit_polls'][$ep_field['id']]['results'][$c_key]['name'] = $candidate['name'];
               $tabulated_results[$s_key][$contest['sanitized_title']]['exit_polls'][$ep_field['id']]['results'][$c_key]['party'] = $candidate['party'];
               $tabulated_results[$s_key][$contest['sanitized_title']]['exit_polls'][$ep_field['id']]['results'][$c_key]['votes'][$ep_key] = sizeof($pollees);
             }
           }
         }
       }

        //Tabulate issue-based question results
       if (!empty($contest['question'])) {
          //Include question and title in tabulated results array
         $tabulated_results[$s_key][$contest['sanitized_title']]['question'] = $contest['question'];
         $tabulated_results[$s_key][$contest['sanitized_title']]['title'] = $contest['title'];

          //Tabulate results for each issue question
         foreach ($contest['options'] as $o_key => $option) {
           $ballots = count_votes($election_id, $contest['sanitized_title'], $option);

            //Results without exit poll breakdown
           $tabulated_results[$s_key][$contest['sanitized_title']]['results'][$o_key]['name'] = $option;
           $tabulated_results[$s_key][$contest['sanitized_title']]['results'][$o_key]['votes'] = sizeof($ballots);

            //Results broken down by exit poll
           foreach ($ep_fields as $ep_field) {
             foreach ($ep_field['options'] as $ep_key => $ep_option) {
               $pollees = count_pollees($election_id, $ep_field['id'], $ep_key, $ballots);
               $tabulated_results[$s_key][$contest['sanitized_title']]['results'][$o_key]['exit_polls'][$ep_field['id']]['title'] = $ep_field['name'];
               $tabulated_results[$s_key][$contest['sanitized_title']]['results'][$o_key]['exit_polls'][$ep_field['id']]['results'][$ep_key] = sizeof($pollees);
             }
           }
         }
       }
     }


      //Convert votes array to results array so each row is in consistent column order
     $results['columns'] = array_merge($columns_contests, $columns_eps);
      foreach ($votes as $v_key => $vote) {
        $results[$s_key]['results'][$v_key] = array();
     
       //  Add vote results to array
        foreach ($section as $contest) {
          if (is_array($vote[$contest['sanitized_title']])) {
            $results[$s_key]['results'][$v_key][] = implode(', ', $vote[$contest['sanitized_title']]);
          }
        }
     
         //Add exit poll answers to results array
        foreach ($ep_fields as $ep_field) {
          $results[$s_key]['results'][$v_key][] = $vote[$ep_field['id']];
        }
      }
   }

   $all_results = [
     'results' => $results,
     'tabulated' => $tabulated_results,
     'participation' => $participation
   ];

   update_option('all_results', json_encode($all_results));

   return $all_results;
 }

add_filter( 'posts_where', 'title_like_posts_where', 10, 2 );
function title_like_posts_where( $where, &$wp_query ) {
    global $wpdb;
    if ( $post_title_like = $wp_query->get( 'post_title_like' ) ) {
        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'' . esc_sql( $wpdb->esc_like( $post_title_like ) ) . '%\'';
    }
    return $where;
}