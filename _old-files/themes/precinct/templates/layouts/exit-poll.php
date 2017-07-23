<?php

use Roots\Sage\Assets;
use Roots\Sage\CMB;

$precinct_name = get_bloginfo('name');
$precinct_id = substr( strrchr( get_bloginfo('url'), '/nc-' ), 4 );
$ballot_id = $_GET['post_submitted'];
?>

<h1 class="h3">Thank you for voting! Before you go, please fill out this exit poll.</h1>

<?php
$exit_poll = CMB\get_exit_poll_object();

$output = "";

// Get any submission errors
if ( ( $error = $exit_poll->prop( 'submission_error' ) ) && is_wp_error( $error ) ) {
  $output .= '<h3>' . sprintf( 'There was an error in the submission: %s', '<strong>'. $error->get_error_message() .'</strong>' ) . '</h3>';
}

// Display metabox on page (changing save button text)
$output .= cmb2_get_metabox_form( $exit_poll, 'fake-oject-id', array( 'save_button' => 'Submit Poll' ) );

echo $output;
?>
