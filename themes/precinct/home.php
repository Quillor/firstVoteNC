<?php

use Roots\Sage\Assets;

?>

<?php
get_template_part('templates/components/title');

  /**
   * When election is live
   *
   *
   *
   */

 // Display form to add new election
 if (isset($_GET['add'])) {
   // Check if the user has permissions to add elections
   if ( ! current_user_can( 'editor' ) ) {
     wp_redirect( get_bloginfo('url') );
     exit;
   }

   get_template_part('/templates/layouts/add-election');

   return false;
 }

// Display teacher dashboard
if ( isset( $_GET['manage'] ) ) {
  // Check if the user has permissions to edit elections
  if ( ! is_user_logged_in() ) {
    wp_redirect( get_bloginfo('url') );
    exit;
  }

  get_template_part('/templates/layouts/teacher-dashboard');

  return false;
}

get_template_part('/templates/layouts/student-precinct');

// I Voted! overlay with TurboVote signup
if ( isset( $_GET['thank_you'] ) ) {
  get_template_part('/templates/layouts/i-voted');
}
?>
