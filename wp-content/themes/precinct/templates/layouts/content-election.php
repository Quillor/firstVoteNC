<article <?php post_class(); ?>>

  <div class="entry-summary">
    <?php
	
	// Display live ballot
    //get_template_part('/templates/layouts/ballot');

    if ( isset( $_GET['preview'] ) ) {
      // Display preview ballot
	  echo '<h1>Preview Ballot</h1>';
      get_template_part('/templates/layouts/ballot');
	  return false;
    } elseif ( !isset( $_GET['results'] ) ) {
      // Redirect to results view
      //wp_redirect( add_query_arg('results', 'general') );
      //exit;
    } else {
      // Show results
      //get_template_part('/templates/layouts/results');
    }
    //return false;

    /**
     * When election is live
     *
     *
     *
     */

    // Display exit poll
    if ( isset( $_GET['post_submitted'] ) && ( $post = get_post( absint( $_GET['post_submitted'] ) ) ) ) {
      get_template_part('/templates/layouts/exit-poll');
      return false;
    }

    if ( isset( $_GET['edit'] ) ) { ?>
	<style>
		.nav.nav-tabs{
			display:none;
		}
	</style>
	<?php
      // Check if the user has permissions to edit elections
      if ( ! current_user_can( 'editor' ) ) {
        wp_redirect( get_the_permalink() );
        exit;
      }

      // If edit was saved, delete generated ballot and redirect to non-edit page
    	if ( isset( $_POST['object_id'] ) ) {
        update_post_meta( $_POST['object_id'], '_cmb_generated_ballot', '' );
        $url = esc_url_raw( get_bloginfo('url') );
    		echo "<script type='text/javascript'>window.location.href = '$url?manage'</script>";
    	}

      // Customize ballot settings -- for teachers
      cmb2_metabox_form( '_cmb_election', get_the_id(), ['save_button' => 'Save Election'] );
      return false;
    }
	
	
	// I Voted! overlay with TurboVote signup
	if ( isset( $_GET['thank_you'] ) ) {
	  get_template_part('/templates/layouts/i-voted');
	}
		
    if ( isset( $_GET['results'] ) ) {
      get_template_part('/templates/layouts/results');
      return false;
    }

    // Display live ballot
    get_template_part('/templates/layouts/ballot');
    ?>

  </div>
</article>
