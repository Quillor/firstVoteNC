<?php
if ( false === ($master = get_transient('master_election_' . $election_id))) {

  // Get ID of master election
  $master_election = get_post_meta( $election_id, '_cmb_election', true );
  $precinct = get_blog_details();
  $precinct_id = substr($precinct->path, 4, -1);

  switch_to_blog(1);
    // Get voting day
    $master['voting_day'] = get_post_meta( $master_election, '_cmb_voting_day', true );
    $master['early_voting'] = get_post_meta( $master_election, '_cmb_early_voting', true );
    $master['ballot_xml_file'] = get_post_meta( $master_election, '_cmb_ballot_xml_file', true);

    // Get address of this precinct
    $loc = array();
    $loc[] = get_post_meta($precinct_id, '_cmb_address_1', true);
    $loc[] = get_post_meta($precinct_id, '_cmb_address_2', true);
    $loc[] = get_post_meta($precinct_id, '_cmb_city', true);
    $loc[] = get_post_meta($precinct_id, '_cmb_state', true);
    $loc[] = get_post_meta($precinct_id, '_cmb_zip', true);
    $master['address'] = implode(', ', $loc);
    $master['congressional_district'] = get_post_meta($precinct_id, '_cmb_congressional_district', true);

  restore_current_blog();

  set_transient('master_election_' . $election_id, $master, 6 * HOUR_IN_SECONDS);
}
