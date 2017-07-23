<?php

namespace Roots\Sage\CMB;

locate_template('/lib/google-auth.php', true, true);

function fvnc_elections_cb($field) {
	if (function_exists('google_api_key')) {
		$api_key = google_api_key();

		// Get available elections from Google's Civic Information API
		$api_get = wp_remote_get('https://www.googleapis.com/civicinfo/v2/elections?key=' . $api_key);

		if ( ! is_wp_error( $api_get ) ) {
			$result = json_decode($api_get['body']);

			$term_options = array(
				false => 'Select one'
			);
	    if ( ! empty( $result->elections ) ) {
	        foreach ( $result->elections as $election ) {
	            $term_options[ $election->id ] = $election->name . ': ' . $election->electionDay;
	        }
	    }

	    return $term_options;
		} else {
			echo $api_get->get_error_message();
		}
	}
}

add_action( 'cmb2_init', function() {
	$prefix = '_cmb_';

	/**
	 * Precinct Custom Fields
	 *
	 */

	$cmb_precinct_address = new_cmb2_box([
		'id'           => $prefix . 'address',
		'title'        => 'School Location',
		'object_types' => array( 'precinct' ),
		'context'      => 'normal',
		'priority'     => 'high',
	]);

	$cmb_precinct_address->add_field([
		'name' => 'Official Name',
		'id' => $prefix . 'precinct_name',
		'type' => 'text'
	]);

	$cmb_precinct_address->add_field([
		'name' => 'Address Line 1',
		'id' => $prefix . 'address_1',
		'type' => 'text'
	]);

	$cmb_precinct_address->add_field([
		'name' => 'Address Line 2',
		'id' => $prefix . 'address_2',
		'type' => 'text'
	]);

	$cmb_precinct_address->add_field([
		'name' => 'City',
		'id' => $prefix . 'city',
		'type' => 'text_small'
	]);

	$cmb_precinct_address->add_field([
		'name' => 'State',
		'id' => $prefix . 'state',
		'type' => 'text_small',
		'default' => 'NC'
	]);

	$cmb_precinct_address->add_field([
		'name' => 'Zip',
		'id' => $prefix . 'zip',
		'type' => 'text_small'
	]);

	$cmb_precinct_address->add_field([
		'name' => 'Congressional District Override',
		'id' => $prefix . 'congressional_district',
		'type' => 'text_small'
	]);


	/**
	 * Election Custom Fields
	 *
	 */

	$cmb_election_box = new_cmb2_box([
		'id'           => $prefix . 'election',
		'title'        => 'Election',
		'object_types' => array( 'election' ),
		'context'      => 'normal',
		'priority'     => 'high',
	]);

	// $cmb_election_box->add_field([
	// 	'name' => 'Election',
	// 	'id' => $prefix . 'election',
	// 	'type' => 'select',
	// 	'options_cb' => __NAMESPACE__ . '\\fvnc_elections_cb'
	// ]);

	$cmb_election_box->add_field([
		'name' => 'Voting Day',
		'id' => $prefix . 'voting_day',
		'type' => 'text_date'
	]);

	$cmb_election_box->add_field([
		'name' => 'Default Early Voting Start',
		'id' => $prefix . 'early_voting',
		'type' => 'text_date'
	]);

	$cmb_election_box->add_field([
		'name' => 'Statewide Ballot XML File',
		'id' => $prefix . 'ballot_xml_file',
		'type' => 'file'
	]);

});


/**
 * Allow XML file uploads
 */
add_filter('upload_mimes', function($mime_types){
  $mime_types['xml'] = 'application/xml'; //Adding xml extension
  return $mime_types;
}, 1, 1);
