<?php

namespace Roots\Sage\CMB;

add_action( 'cmb2_init', function() {
	$prefix = '_cmb_';

	/**
	 * Add new election
	 */
	$cmb_new_election = new_cmb2_box([
		'id'           => $prefix . 'new_election',
		'title'        => 'New Election',
    'hookup'       => false,
    'save_fields'  => false,
	]);

	$cmb_new_election->add_field([
		'name' => 'Select an election',
		'id' => $prefix . 'election',
		'type' => 'select',
		'options_cb' => __NAMESPACE__ . '\\fvnc_elections_cb'
	]);

	/**
	 * Election options
	 */
	$cmb_election_box = new_cmb2_box([
		'id'           => $prefix . 'election',
		'title'        => 'Election Options',
		'object_types' => array( 'election' ),
		'context'      => 'normal',
		'priority'     => 'high'
	]);

	$cmb_election_box->add_field([
		'name' => 'Voting day',
		'id' => $prefix . 'voting_day',
		'type' => 'text_date',
		'date_format' => 'M j, Y',
		'attributes' => ['disabled' => 'disabled']
	]);

	$cmb_election_box->add_field([
		'name' => 'Set early voting start date',
		'id' => $prefix . 'early_voting',
		'type' => 'text_date',
		'date_format' => 'M j, Y',
		'description' => 'Polls will be open from 7:30am to 7:30pm each day during the early voting period and on election day.',
	]);

	$cmb_election_box->add_field([
    'name' => 'Races',
    'before_field' => '<p class="cmb2-metabox-description">Check races to include in this election.</p>',
    'id' => $prefix . 'included_races',
    'type' => 'multicheck',
		'select_all_button' => true,
    'options_cb' => __NAMESPACE__ . '\\races_cb'
	]);

	$custom_races = $cmb_election_box->add_field( array(
		'name' 				=> 'Additional Contests',
    'id'          => $prefix . 'custom_contests',
    'type'        => 'group',
    'description' => 'Enter customized contests for which your students may vote in this simulation election. These will appear at the end of the ballot section selected.',
    // 'repeatable'  => false, // use false if you want non-repeatable group
    'options'     => array(
      'group_title'   => __( 'Contest {#}', 'cmb2' ), // since version 1.1.4, {#} gets replaced by row number
      'add_button'    => __( 'Add Another', 'cmb2' ),
      'remove_button' => __( 'Remove', 'cmb2' ),
      'sortable'      => true, // beta
      // 'closed'     => true, // true to have the groups closed by default
    ),
	) );

	// Id's for group's fields only need to be unique for the group. Prefix is not needed.
	$cmb_election_box->add_group_field( $custom_races, array(
    'name' => 'Title',
    'id'   => 'title',
    'type' => 'text',
    // 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
	) );

	$cmb_election_box->add_group_field( $custom_races, array(
    'name' => 'Votes Allowed',
    'id'   => 'votes_allowed',
    'type' => 'text',
	) );

	$cmb_election_box->add_group_field( $custom_races, array(
    'name' => 'Section',
    'id'   => 'section',
    'type' => 'radio',
		'options' => array(
			'Partisan Offices' => 'Partisan',
			'Nonpartisan Offices' => 'Nonpartisan'
		)
	) );

	$cmb_election_box->add_group_field( $custom_races, array(
    'name' => 'Candidates',
    'id'   => 'candidates',
    'type' => 'text',
	) );

	$candidates = $cmb_election_box->add_group_field( $custom_races, array(
    'name' => 'Candidates',
    'id'   => 'candidates',
    'type' => 'textarea_small',
		'description' => 'Enter one candidate on each line with their party in parentheses.'
	) );

	// $cmb_election_box->add_field([
  //   'name' => 'Referenda',
  //   'before_field' => '<p class="cmb2-metabox-description">Check referenda to include in this election.</p>',
  //   'id' => $prefix . 'included_referenda',
  //   'type' => 'multicheck',
	// 	'select_all_button' => true,
  //   'options_cb' => __NAMESPACE__ . '\\referenda_cb',
	// ]);

	$custom_questions = $cmb_election_box->add_field( array(
		'name' 				=> 'Additional Custom Questions',
    'id'          => $prefix . 'custom_questions',
    'type'        => 'group',
    'description' => 'Enter customized questions (yes/no answers) for which students may vote in this simulation election.',
    // 'repeatable'  => false, // use false if you want non-repeatable group
    'options'     => array(
      'group_title'   => __( 'Question {#}', 'cmb2' ), // since version 1.1.4, {#} gets replaced by row number
      'add_button'    => __( 'Add Another', 'cmb2' ),
      'remove_button' => __( 'Remove', 'cmb2' ),
      'sortable'      => true, // beta
      // 'closed'     => true, // true to have the groups closed by default
    ),
	) );

	// Id's for group's fields only need to be unique for the group. Prefix is not needed.
	$cmb_election_box->add_group_field( $custom_questions, array(
    'name' => ' Title',
    'id'   => 'title',
    'type' => 'text',
    // 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
	) );

	$cmb_election_box->add_group_field( $custom_questions, array(
    'name' => 'Question',
    'id'   => 'question',
    'type' => 'textarea_small',
	) );
});

/**
 * Callback function that gets the master elections from the main site as options
 *
 */
function fvnc_elections_cb($field) {
	// Save original post data in variable
	global $post;
	$original = $post;

	// Switch to main site to query master elections
  switch_to_blog(1);

		$elections = new \WP_Query([
			'post_type' => 'election',
			'posts_per_page' => -1
		]);

		$term_options = [ false => 'Select one' ];

		if ($elections->have_posts()) : while ($elections->have_posts()) : $elections->the_post();
			$term_options[ get_the_id() ] = get_the_title();
		endwhile; endif; wp_reset_postdata();

	restore_current_blog();

	// Reset post data (because wp_reset_postdata() isn't doing the trick)
	$post = $original;

	return $term_options;
}

/**
 * Callback function that lists races on the ballot
 *
 */
function races_cb($field) {
	$ballot_json = get_post_meta(get_the_id(), '_cmb_ballot_json', true);

	$ballot = json_decode($ballot_json);
	$options = [];
	// loop through ballot sections
	foreach($ballot as $section) {
		if ($section->section !== 'Referenda') {
			// create option for each contest
			foreach ($section->races as $contest) {
				$options[$contest->ballot_title] = $contest->ballot_title;
			}
		}
	}

	return $options;
}

/**
 * Callback function that lists referenda on the ballot
 *
 */
function referenda_cb($field) {
	$contests = get_post_meta(get_the_id(), '_cmb_contests', true);

	$options = [];
	foreach($contests as $contest) {
		if ($contest->type == 'Referendum') {
			$options[$contest->referendumTitle] = $contest->referendumTitle;
		}
	}

	return $options;
}


function get_election_info() {
	include( locate_template( '/lib/transient-election.php' ) );
}
add_action( 'cmb2_before_post_form__cmb_election', __NAMESPACE__ . '\\get_election_info' );


/**
 * Gets the front-end-post-form cmb instance
 *
 * @return CMB2 form
 */
function get_election_form() {
  $metabox_id = '_cmb_new_election';
  $object_id = 'fake-oject-id'; // since post ID will not exist yet, just need to pass it something
  return cmb2_get_metabox( $metabox_id, $object_id );
}


/**
 * Handles form submission on save. Redirects if save is successful, otherwise sets an error message as a cmb property
 *
 * @return void
 */
 // Add election
add_action( 'cmb2_after_init', function() {
  // If no form submission, bail
  if ( empty( $_POST ) || ! isset( $_POST['submit-cmb'], $_POST['object_id'] ) )
  	return false;

	// Only do this for new election form
	if (!isset($_GET['add']) || $_POST['_cmb_election'] == 0)
		return false;

  // Get CMB2 metabox object
  $election = get_election_form();

  // Check security nonce
  if ( ! isset( $_POST[ $election->nonce() ] ) || ! wp_verify_nonce( $_POST[ $election->nonce() ], $election->nonce() ) ) {
  	return $election->prop( 'submission_error', new \WP_Error( 'security_fail', __( 'Security check failed.' ) ) );
  }

	// Get title of master election
  $election_id = $_POST['_cmb_election'];
  switch_to_blog(1);
	  $election_name = get_the_title($election_id);
	  $master['voting_day'] = get_post_meta( $election_id, '_cmb_voting_day', true );
	  $master['early_voting'] = get_post_meta( $election_id, '_cmb_early_voting', true );
  restore_current_blog();

  // Set post_data for saving new post
  $post_data = array(
    'post_author' => 1, // Admin
    'post_status' => 'publish',
    'post_type'   => 'election',
		'post_title'  => $election_name
  );

  // Create the new post
  $new_election_id = wp_insert_post( $post_data, true );
  
  // Set post_data for saving new post for VOTES and CONTEST DATA
  $votes_contest = array(
    'post_author' => 1, // Admin
    'post_status' => 'publish',
    'post_type'   => 'votes_contest',
	'post_title'  => $new_election_id
  );
  // Create the new post for votes and contest
  wp_insert_post( $votes_contest);

  // If we hit a snag, update the user
  if ( is_wp_error( $new_election_id )) {
  	return $election->prop( 'submission_error', $new_election_id );
  }

	// Set custom post meta for election dates
  
  update_post_meta($election_id, '_cmb_voting_day', $master['voting_day']);
  update_post_meta($election_id, '_cmb_early_voting', $master['early_voting']);

  // Loop through post data and save sanitized data to post-meta
  foreach ( $_POST as $key => $value ) {
    if( substr($key, 0, 5) == '_cmb_' ) {
    	if ( is_array( $value ) ) {
    		$value = array_filter( $value );
    		if( ! empty( $value ) ) {
    			update_post_meta( $new_election_id, $key, esc_html($value) );
    		}
    	} else {
    		update_post_meta( $new_election_id, $key, esc_html($value) );
    	}
    }
  }

  /*
   * Redirect back to the form page with a query variable with the new post ID.
   * This will help double-submissions with browser refreshes
   */
  wp_redirect( get_permalink($new_election_id) . '?edit' );
  exit;
} );
