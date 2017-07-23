<?php

namespace Roots\Sage\CMB;

add_action( 'cmb2_init', function() {

	include(locate_template('/lib/fields-exit-poll.php'));

  /**
   * Exit poll front end submission
   */
  $exitpoll = new_cmb2_box([
    'id'           => $prefix . 'exit_poll_form',
    'object_types' => array( 'exit-poll' ),
    'hookup'       => false,
    'save_fields'  => false,
  ]);

	$exitpoll->add_field([
		'id' => $prefix . 'hidden_fields',
		'type' => 'text',
		'render_row_cb' => __NAMESPACE__ . '\\hidden_fields_cb'
	]);

	/**
	 * Exit poll display on backend
	 */
	$cmb_exitpoll_box = new_cmb2_box([
		'id'           => $prefix . 'exitpoll',
		'title'        => 'Exit Poll',
		'object_types' => array( 'exit-poll' ),
		'context'      => 'normal',
		'priority'     => 'high'
	]);

	$cmb_exitpoll_box->add_field([
		'name' => 'Election',
		'id' => $prefix . 'election_id',
		'type' => 'text',
		// 'attributes' => ['disabled' => 'disabled'],
		'column' => [
			'position' => 2,
			'name' => 'Election'
		]
	]);

	$cmb_exitpoll_box->add_field([
		'name' => 'Ballot',
		'id' => $prefix . 'ballot_id',
		'type' => 'text',
		// 'attributes' => ['disabled' => 'disabled'],
		'column' => [
			'position' => 3,
			'name' => 'Ballot'
		]
	]);

	foreach ($ep_fields as $field) {
		// $field['attributes'] = ['disabled' => 'disabled'];
		$exitpoll->add_field($field);
		$cmb_exitpoll_box->add_field($field);
	}

});

function accessible_fields_cb($field_args, $field) {
	printf( "<div class=\"cmb-row %s\">\n", $field->row_classes() );
	echo '<fieldset>';
	echo '<legend>', $field->args( 'name' ), '</legend>';
	echo "\n\t<div class=\"cmb-td\">\n";
	$field_type = new \CMB2_Types( $field );
  $field_type->render();
	echo "\n\t</div>\n</fieldset>\n</div>";

	return $field;
}

function hidden_fields_cb() {
  // Create hidden field that includes value of election_id
	$election_id = get_post_meta(get_the_id(), '_cmb_election_id', true);

  echo '<input type="hidden" name="_cmb_election_id" id="_cmb_election_id" value="' . $election_id . '" />';
  echo '<input type="hidden" name="_cmb_ballot_id" id="_cmb_ballot_id" value="' . get_the_id() . '" />';
}

/**
 * Gets the front-end-post-form cmb instance
 *
 * @return CMB2 object
 */
function get_exit_poll_object() {
  $metabox_id = '_cmb_exit_poll_form';
  $object_id = 'fake-oject-id'; // since post ID will not exist yet, just need to pass it something
  return cmb2_get_metabox( $metabox_id, $object_id );
}


/**
 * Handles form submission on save. Redirects if save is successful, otherwise sets an error message as a cmb property
 *
 * @return void
 */
add_action( 'cmb2_after_init', function() {
  // If no form submission, bail
  if ( empty( $_POST ) || ! isset( $_POST['submit-cmb'], $_POST['object_id'] ) ) {
  	return false;
  }

  // Get CMB2 metabox object
  $exitpoll = get_exit_poll_object();

  // Set post_data for saving new post
  $post_data = array(
    'post_author' => 1, // Admin
    'post_status' => 'publish',
    'post_type'   => 'exit-poll'
  );

  // Check security nonce
  if ( ! isset( $_POST[ $exitpoll->nonce() ] ) || ! wp_verify_nonce( $_POST[ $exitpoll->nonce() ], $exitpoll->nonce() ) ) {
  	return $exitpoll->prop( 'submission_error', new \WP_Error( 'security_fail', __( 'Security check failed.' ) ) );
  }

  // Create the new post
  $new_exitpoll_id = wp_insert_post( $post_data, true );

  // Update title to the ID
  wp_update_post([
    'ID'           => $new_exitpoll_id,
    'post_title'   => $new_exitpoll_id
  ]);

  // If we hit a snag, update the user
  if ( is_wp_error( $new_exitpoll_id ) ) {
  	return $exitpoll->prop( 'submission_error', $new_exitpoll_id );
  }

  // Loop through post data and save sanitized data to post-meta
  foreach ( $_POST as $key => $value ) {
    if( substr($key, 0, 5) == '_cmb_' ) {
    	if ( is_array( $value ) ) {
    		$value = array_filter( $value );
    		if( ! empty( $value ) ) {
    			update_post_meta( $new_exitpoll_id, $key, esc_html($value) );
    		}
    	} else {
    		update_post_meta( $new_exitpoll_id, $key, esc_html($value) );
    	}
    }
  }

  /*
   * Redirect back to the form page with a query variable with the new post ID.
   * This will help double-submissions with browser refreshes
   */
  wp_redirect( esc_url_raw( add_query_arg( ['post_submitted' => null, 'thank_you' => '0'], get_bloginfo('url') ) ) );
  exit;
} );


/*
 * Plugin Name: CMB2 js validation for "required" fields
 * Description: Uses js to validate CMB2 fields that have the 'data-validation' attribute set to 'required'
 * Version: 0.1.0
 *
 * Documentation in the wiki:
 * @link https://github.com/WebDevStudios/CMB2/wiki/Plugin-code-to-add-JS-validation-of-%22required%22-fields
 */

add_action( 'cmb2_after_form', function( $post_id, $cmb ) {
	static $added = false;

  // Only add this to the exit poll
  if (!isset($_GET['post_submitted'])) {
    return;
  }

	// Only add this to the page once (not for every metabox)
	if ( $added ) {
		return;
	}

	$added = true;
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {

		$form = $( document.getElementById( '_cmb_exit_poll_form' ) );
		$htmlbody = $( 'html, body' );
		$toValidate = $( '[data-validation]' );

		if ( ! $toValidate.length ) {
			return;
		}

		function checkValidation( evt ) {
			var labels = [];
			var $first_error_row = null;
			var $row = null;

			function add_required( $row ) {
				$row.addClass('error');
				$first_error_row = $first_error_row ? $first_error_row : $row;
				labels.push( $row.find( '.cmb-th label' ).text() );
			}

			function remove_required( $row ) {
				$row.removeClass('error');
			}

			$toValidate.each( function() {
				var $this = $(this);
				$row = $this.parents( '.cmb-row' );
        var val = '';
        if ($this.attr('type') == 'radio') {
          val = $row.find('input:radio:checked').val();
        } else {
  				val = $this.val();
        }

				if ( $this.is( '[type="button"]' ) || $this.is( '.cmb2-upload-file-id' ) ) {
					return true;
				}

				if ( 'required' === $this.data( 'validation' ) ) {
					if ( $row.is( '.cmb-type-file-list' ) ) {

						var has_LIs = $row.find( 'ul.cmb-attach-list li' ).length > 0;

						if ( ! has_LIs ) {
							add_required( $row );
						} else {
							remove_required( $row );
						}

					} else {
						if ( ! val ) {
							add_required( $row );
						} else {
							remove_required( $row );
						}
					}
				}

			});

			if ( $first_error_row ) {
				evt.preventDefault();
				alert( 'Please answer the required questions.' );
				$htmlbody.animate({
					scrollTop: ( $first_error_row.offset().top - 200 )
				}, 1000);
			}

		}

		$form.on( 'submit', checkValidation );
	});
	</script>
	<?php
}, 10, 2 );
