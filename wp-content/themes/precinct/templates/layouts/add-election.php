<?php

use Roots\Sage\CMB;

$form = CMB\get_election_form();

// Flush rewrite rules
global $wp_rewrite;
$wp_rewrite->init();
$wp_rewrite->flush_rules();
?>
<div class="container add-election">
  <div class="content">
    <main class="main">
      <?php
      $output = "";

      // Get any submission errors
      if ( ( $error = $form->prop( 'submission_error' ) ) && is_wp_error( $error ) ) {
        $output .= '<h3>' . sprintf( 'There was an error in the submission: %s', '<strong>'. $error->get_error_message() .'</strong>' ) . '</h3>';
      }

      // Display metabox on page (changing save button text)
      $output .= cmb2_get_metabox_form( $form, 'fake-oject-id', array( 'save_button' => 'Add Election' ) );

      echo $output;
      ?>
    </main>
  </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
  $('#_cmb_new_election').on('submit', function(e) {
    if ($('#loading').length) {
      // If form already submitted, don't do it again.
      e.preventDefault();
    } else {
      // Add loading screen after submit
      $('body').append('<div id="loading" class="loading"><div class="loader"></div></div>');
    }
  });
});
</script>
