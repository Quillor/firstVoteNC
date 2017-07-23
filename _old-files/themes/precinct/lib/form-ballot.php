<?php

namespace Roots\Sage\CMB;

use Roots\Sage\Extras;

add_action( 'cmb2_init', function() {

  $prefix = '_cmb_';

	/**
	 * Ballot display on backend
	 */

 	$cmb_ballot_box = new_cmb2_box([
 		'id'           => $prefix . 'ballot',
 		'title'        => 'Ballot',
 		'object_types' => array( 'ballot' ),
 		'context'      => 'normal',
 		'priority'     => 'high',
 	]);

	$cmb_ballot_box->add_field([
		'name' => 'Election',
		'id' => $prefix . 'election_id',
		'type' => 'text',
		'attributes' => ['disabled' => 'disabled'],
		'column' => [
			'position' => 2,
			'name' => 'Election'
		]
	]);

  $cmb_ballot_box->add_field([
		'id'   => $prefix . 'races',
    'name' => 'Races',
    'type' => 'text',
		'render_row_cb' => __NAMESPACE__ . '\\make_races_cb'
  ]);

  /**
   * Register the form and fields for our front-end submission form
   */
  $ballot = new_cmb2_box([
    'id'           => $prefix . 'voter_ballot_form',
    'object_types' => array( 'ballot' ),
    'hookup'       => false,
    'save_fields'  => false,
  ]);

  $ballot->add_field( array(
    'id'   => $prefix . 'races',
    'name' => 'Races',
    'type' => 'text',
    'render_row_cb' => __NAMESPACE__ . '\\make_races_cb'
  ) );

});


/**
 * Manually render a field.
 *
 * @param  array      $field_args Array of field arguments.
 * @param  CMB2_Field $field      The field object
 */
function make_races_cb($field_args, $field) {

  include(locate_template('/lib/fields-ballot-init.php'));

  if (!is_array($included_races))
    return false;

  // Create hidden field that includes value of election_id
  if (get_post_type() == 'election') {
    echo '<input type="hidden" name="_cmb_election_id" id="_cmb_election_id" value="' . $election_id . '" />';
  }

  // This first part is only for actually casting a vote
  if (get_post_type() == 'election') {

    // Render each race with matching data from the generated ballot
    // $generated_ballot = get_post_meta( $election_id, '_cmb_generated_ballot', true);

    if (empty($generated_ballot)) {
      ob_start();
        foreach ($ballot_data as $ballot_section) {
          $z = 0;
          foreach ($ballot_section->races as $race) {
            // Find this race in the election data
            $key = array_search($race->ballot_title, $included_races);
            if ($key !== FALSE && $race->votes_allowed > 0) {
              // Print the section title if this is the first race in section
              if ($z == 0) {
                echo '<h2 class="section-head h6">';
                  echo $ballot_section->section;
                echo '</h2>';
              }

              if ( (int) $race->votes_allowed > 1 ) {
                $type = 'checkbox';
                $array = true;
              } else {
                $type = 'radio';
                $array = false;
              }
              // $number = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);

              // Set sanitized title
              if (!empty($race->seat)) {
                $sanitized_title = sanitize_title($race->ballot_title . '-' . $race->seat);
              } else {
                $sanitized_title = sanitize_title($race->ballot_title);
              }

              // Set district
              $district = '';
              if (!empty($race->district)) {
                $district = '<br />' . $race->district;
              }
              ?>
              <div class="cmb-row cmb2-id-<?php echo $sanitized_title; ?>">
                <fieldset>
                  <div class="contest-head">
                    <legend class="h3"><?php echo str_replace(['(',')'], ['<br /><span>','</span>'], $race->ballot_title) . $district; ?></legend>
                    <p>(You may vote for <?php /*echo $number->format($race->votes_allowed);*/ echo $race->votes_allowed; ?>)</p>
                  </div>

                  <ul class="cmb2-<?php echo $type; ?>-list no-select-all cmb2-list">
                    <?php
                    $j = 0;
                    foreach ($race->candidates as $c) {
                      $c->party = str_replace(' Party', '', $c->party);
                      switch ($c->party) {
                        case 'Democratic':
                          $party = 'Democrat';
                          break;
                        case '':
                          $party = 'Unaffiliated';
                          break;
                        default:
                          $party = $c->party;
                          break;
                      }
                      $c->ballotName = str_replace('\\"', '&quot;', $c->ballotName);
                      ?>
                      <li>
                        <label for="<?php echo $sanitized_title . '-' . $j; ?>">
                          <input type="<?php echo $type; ?>" class="cmb2-option" name="_cmb_ballot_<?php echo $sanitized_title; ?><?php if ($array) {echo '[]';} ?>" id="<?php echo $sanitized_title . '-' . $j; ?>" value="<?php echo $c->ballotName; ?>" aria-label="<?php echo str_replace('<br />', ' ', $c->ballotName) . ' ' . $party; ?>">
                          <span aria-hidden="true"><?php echo $c->ballotName; ?></span>
                          <br />
                          <span class="small" aria-hidden="true"><?php if ($race->partisan == 'true') { echo $party; } else { echo '&nbsp;'; } ?></span>
                        </label>
                      </li>
                      <?php
                      $j++;
                    } ?>
                    <li>
                      <label for="<?php echo $sanitized_title; ?>">
                        <input data-validation="required" type="<?php echo $type; ?>" class="cmb2-option" name="_cmb_ballot_<?php echo $sanitized_title; ?><?php if ($array) {echo '[]';} ?>" id="<?php echo $sanitized_title; ?>" value="none" aria-label="No Selection">
                        <span aria-hidden="true">No Selection</span>
                      </label>
                    </li>
                  </ul>
                </fieldset>
              </div>
              <?php
              $z++;
            }
          }

          $custom_match = Extras\array_find_deep($custom, $ballot_section->section);

          foreach ($custom_match as $match_contest) {
            $contest = $custom[$match_contest[0]];
            if (!empty($contest['title'])) {
              if ( (int) $contest['votes_allowed'] > 1 ) {
                $type = 'checkbox';
                $array = true;
              } else {
                $type = 'radio';
                $array = false;
              }

              ?>
              <div class="cmb-row cmb2-id-<?php echo sanitize_title($contest['title']); ?>" data-max="<?php echo $contest['votes_allowed']; ?>">
                <fieldset>
                  <div class="contest-head">
                    <legend class="h3"><?php echo $contest['title']; ?></legend>
                    <p>(You may vote for <?php /*echo $number->format($race['votes_allowed']);*/ echo $contest['votes_allowed']; ?>)</p>
                  </div>

                  <ul class="cmb2-<?php echo $type; ?>-list no-select-all cmb2-list">
                    <?php
                    $candidates = explode("\n", str_replace("\r", "", $contest['candidates']));
                    $m = 0;
                    foreach ($candidates as $candidate) {
                      $listing = str_replace([' (',')'], ['</span><br /><span class="small" aria-hidden="true">',''], $candidate);
                      if ($listing == $candidate) {
                        $listing = $candidate . '</span><br /><span class="small" aria-hidden="true">&nbsp;';
                      }
                      ?>
                      <li>
                        <label for="<?php echo sanitize_title($contest['title']) . '-' . $m; ?>">
                          <input type="<?php echo $type; ?>" class="cmb2-option" name="_cmb_ballot_<?php echo sanitize_title($contest['title']); ?><?php if ($array) {echo '[]';} ?>" id="<?php echo sanitize_title($contest['title']) . '-' . $m; ?>" value="<?php echo $candidate; ?>" aria-label="<?php echo $candidate; ?>">
                          <span aria-hidden="true"><?php echo $listing; ?></span>
                        </label>
                      </li>
                      <?php
                      $m++;
                    }
                    ?>
                    <li>
                      <label for="<?php echo sanitize_title($contest['title']); ?>">
                        <input data-validation="required" type="<?php echo $type; ?>" class="cmb2-option" name="_cmb_ballot_<?php echo sanitize_title($contest['title']); ?><?php if ($array) {echo '[]';} ?>" id="<?php echo sanitize_title($contest['title']); ?>" value="none" aria-label="No selection">
                        <span aria-hidden="true">No Selection</span>
                      </label>
                    </li>
                  </ul>
                </fieldset>
              </div>
              <?php
            }
          }
        }

        echo '<h2 class="section-head h6">Issues</h2>';
        $k = 0;

        foreach ($issues as $question) {
          if (!empty($question)) {
            ?>
            <div class="cmb-row cmb2-id-<?php echo sanitize_title($question['title']); ?>-<?php echo $k; ?>">
              <fieldset>
                <div class="contest-head">
                  <legend class="h3"><?php echo $question['title']; ?></legend>
                </div>

                <p class="issue-question"><?php echo $question['question']; ?></p>

                <ul class="cmb2-radio-list cmb2-list">
                  <?php if (empty($question['options'])) { ?>
                    <li>
                      <label for="<?php echo sanitize_title($question['title']); ?>-0">
                        <input data-validation="required" type="radio" class="cmb2-option" name="_cmb_ballot_<?php echo sanitize_title($question['title']); ?>-<?php echo $k; ?>" id="<?php echo sanitize_title($question['title']); ?>-0" value="Yes" aria-label="Yes">
                        <span aria-hidden="true">Yes</span>
                      </label>
                    </li>
                    <li>
                      <label for="<?php echo sanitize_title($question['title']); ?>-1">
                        <input data-validation="required" type="radio" class="cmb2-option" name="_cmb_ballot_<?php echo sanitize_title($question['title']); ?>-<?php echo $k; ?>" id="<?php echo sanitize_title($question['title']); ?>-1" value="No" aria-label="No">
                        <span aria-hidden="true">No</span>
                      </label>
                    </li>
                  <?php } else {
                    $l = 0;
                    foreach ($question['options'] as $option) { ?>
                      <li>
                        <label for="<?php echo sanitize_title($question['title']); ?>-<?php echo $l; ?>">
                          <input data-validation="required" type="radio" class="cmb2-option" name="_cmb_ballot_<?php echo sanitize_title($question['title']); ?>-<?php echo $k; ?>" id="<?php echo sanitize_title($question['title']); ?>-<?php echo $l; ?>" value="<?php echo $option; ?>" aria-label="<?php echo $option; ?>">
                          <span aria-hidden="true"><?php echo $option; ?></span>
                        </label>
                      </li>
                      <?php
                      $l++;
                    }
                  } ?>
                </ul>
              </fieldset>
            </div>
            <?php
            $k++;
          }
        }
      $generated_ballot = ob_get_clean();

      // Save ballot to database
      // update_post_meta( $election_id, '_cmb_generated_ballot', $generated_ballot );
    }

    // Display ballot
    echo $generated_ballot;
    echo '<div class="end contest-head">End of Ballot</div>';

  } elseif (get_post_type() == 'ballot') {
    // Just display results here
    foreach ($ballot_data as $ballot_section) {
      echo '<h4 class="section-head h6">';
        echo $ballot_section->section;
      echo '</h4>';
      foreach ($ballot_section->races as $race) {
        $key = array_search($race->ballot_title, $included_races);
        if ($key !== FALSE && $race->votes_allowed > 0) {
          if (!empty($race->seat)) {
            $sanitized_title = sanitize_title($race->ballot_title . '-' . $race->seat);
          } else {
            $sanitized_title = sanitize_title($race->ballot_title);
          }
          ?>
          <div class="cmb-row cmb2-id-<?php echo $sanitized_title; ?>">
            <fieldset>
              <div class="contest-head">
                <legend class="h3"><?php echo $race->ballot_title; ?></legend>
              </div>

              <?php
              if ( (int) $race->votes_allowed > 1 ) {
                $results = get_post_meta(get_the_id(), '_cmb_ballot_' . sanitize_title($race['ballot_title']), true);
                $results = unserialize(html_entity_decode($results));
                foreach ($results as $result) {
                  ?>
                  <input type="text" name="_cmb_ballot_<?php echo $sanitized_title; ?>[]" value="<?php echo $result; ?>" disabled="disabled" />
                  <?php
                }
              } else {
                ?>
                <input type="text" name="_cmb_ballot_<?php echo $sanitized_title; ?>" value="<?php echo stripslashes(esc_html(get_post_meta(get_the_id(), '_cmb_ballot_' . $sanitized_title, true))); ?>" disabled="disabled" />
                <?php
              }
              ?>
            </fieldset>
          </div>
          <?php
        }
      }
    }
    foreach ($custom as $contest) {
      ?>
      <div class="cmb-row cmb2-id-<?php echo sanitize_title($contest['title']); ?>">
        <fieldset>
          <div class="contest-head">
            <legend class="h3"><?php echo $contest['title']; ?></legend>
          </div>

          <?php
          if ( (int) $contest['votes_allowed'] > 1 ) {
            $results = get_post_meta(get_the_id(), '_cmb_ballot_' . sanitize_title($contest['title']), true);
            $results = unserialize(html_entity_decode($results));
            foreach ($results as $result) {
              ?>
              <input type="text" name="_cmb_ballot_<?php echo sanitize_title($contest['title']); ?>[]" value="<?php echo $result; ?>" disabled="disabled" />
              <?php
            }
          } else {
            ?>
            <input type="text" name="_cmb_ballot_<?php echo sanitize_title($contest['title']); ?>" value="<?php echo esc_html(get_post_meta(get_the_id(), '_cmb_ballot_' . sanitize_title($contest['title']), true)); ?>" disabled="disabled" />
            <?php
          }
          ?>
        </fieldset>
      </div>
      <?php
    }
    $k = 0;
    foreach ($issues as $question) {
      ?>
      <div class="cmb-row cmb2-id-<?php echo sanitize_title($question['title']); ?>-<?php echo $k; ?>">
        <fieldset>
          <div class="contest-head">
            <legend class="h3"><?php echo $question['title']; ?></legend>
          </div>

          <p class="issue-question"><?php echo $question['question']; ?></p>

          <input type="text" name="_cmb_ballot_<?php echo sanitize_title($question['title']); ?>-<?php echo $k; ?>" value="<?php echo esc_html(get_post_meta(get_the_id(), '_cmb_ballot_' . sanitize_title($question['title']) . '-' . $k, true)); ?>" disabled="disabled" />
        </fieldset>
      </div>
      <?php
      $k++;
    }
  }
}


/**
 * Gets the front-end-post-form cmb instance
 *
 * @return CMB2 object
 */
function get_voter_ballot_object() {
  $metabox_id = '_cmb_voter_ballot_form';
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
  $ballot = get_voter_ballot_object();

  // Set post_data for saving new post
  $post_data = array(
    'post_author' => 1, // Admin
    'post_status' => 'publish',
    'post_type'   => 'ballot'
  );

  // Check security nonce
  if ( ! isset( $_POST[ $ballot->nonce() ] ) || ! wp_verify_nonce( $_POST[ $ballot->nonce() ], $ballot->nonce() ) ) {
  	return $ballot->prop( 'submission_error', new \WP_Error( 'security_fail', __( 'Security check failed.' ) ) );
  }

  // Create the new post
  $new_vote_id = wp_insert_post( $post_data, true );

  // Update title to the ID
  wp_update_post([
    'ID'           => $new_vote_id,
    'post_title'   => $new_vote_id
  ]);

  // If we hit a snag, update the user
  if ( is_wp_error( $new_vote_id ) ) {
  	return $ballot->prop( 'submission_error', $new_vote_id );
  }

  // Loop through post data and save sanitized data to post-meta
  foreach ( $_POST as $key => $value ) {
    if( substr($key, 0, 5) == '_cmb_' ) {
    	if ( is_array( $value ) ) {
    		$value = serialize( $value );
    		if( ! empty( $value ) ) {
    			update_post_meta( $new_vote_id, $key, esc_html($value) );
    		}
    	} else {
    		update_post_meta( $new_vote_id, $key, esc_html($value) );
    	}
    }
  }

  /*
   * Redirect back to the form page with a query variable with the new post ID.
   * This will help double-submissions with browser refreshes
   */
  wp_redirect( esc_url_raw( add_query_arg( 'post_submitted', $new_vote_id ) ) );
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

  // Only add this to the ballot
  if (isset($_GET['post_submitted'])) {
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

		$form = $( document.getElementById( '_cmb_voter_ballot_form' ) );
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
        } else if ($this.attr('type') == 'checkbox') {
          val = $row.find('input:checkbox:checked').map(function() { return this.value; }).get();
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
            if ($this.attr('type') == 'checkbox') {
              if (val.length < 1 || val.length > $row.data('max')) {
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
				}

			});

			if ( $first_error_row ) {
				evt.preventDefault();
				alert( 'At least one selection for every contest is required. If you do not wish to cast a vote for a particular contest, mark "No Selection"' );
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
