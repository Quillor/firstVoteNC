<?php

use Roots\Sage\Assets;
use Roots\Sage\CMB;

$precinct_name = get_bloginfo('name');
$precinct_id = substr( strrchr( get_bloginfo('url'), '/nc-' ), 4 );

// Dates when polls are open
$early_voting = new DateTime();
$early_voting->setTimestamp(strtotime(get_post_meta(get_the_id(), '_cmb_early_voting', true)));
$early_voting->setTime(07, 30, 00);

$voting_start = $early_voting->getTimestamp();

$election_day = new DateTime();
$election_day->setTimestamp(strtotime(get_post_meta(get_the_id(), '_cmb_voting_day', true)));
$election_day->setTime(24, 30, 00);

$voting_end = $election_day->getTimestamp();

// Temp/testing timestamp
// $today = new DateTime();
// $today->setDate(2016, 10, 25);
// $today->setTime(9, 00, 00);
// $now = $today->getTimestamp();

// Now timestamp
$now = current_time('timestamp');
$today = new DateTime();
$today->setTimestamp($now);
$today->setTimeZone(new DateTimeZone('America/New_York'));

// // Check if today is during voting period
if ($voting_start <= $now && $now <= $voting_end) {
  // Is it between 7:30am and 7:30pm?
  $open = clone $today;
  $open->setTime(07, 30, 00);
  $close = clone $today;
  $close->setTime(24, 30, 00);

  if ($open->getTimestamp() <= $now && $now <= $close->getTimestamp()) {
    $canvote = true;
  } else {
    $canvote = false;
  }
} else {
  $canvote = false;
}

// CLOSE POLLS
$canvote = false;
?>

<?php if ($canvote === false) { ?>
  <div class="sample-ballot">Sample Ballot</div>
<?php } ?>

<img class="cross-left" src="<?php echo Assets\asset_path('images/ballot-cross.png'); ?>" srcset="<?php echo Assets\asset_path('images/ballot-cross@2x.png'); ?> 2x" alt="" />
<img class="cross-right" src="<?php echo Assets\asset_path('images/ballot-cross.png'); ?>" srcset="<?php echo Assets\asset_path('images/ballot-cross@2x.png'); ?> 2x" alt="" />

<h1 class="sr-only">First Vote NC Ballot</h1>

<div class="ballot-head row sr-mute" aria-hidden="true">
  <div class="col-sm-6">
    <strong>First Vote NC<br />
      <?php echo $precinct_name; ?><br />
      <?php echo date('F j, Y', strtotime(get_post_meta(get_the_id(), '_cmb_voting_day', true))); ?>
    </strong>
  </div>

  <div class="col-sm-6 text-right h2">
    <?php echo 'G' . $precinct_id; ?>
  </div>
</div>

<div class="ballot-wrap <?php if ($canvote === false) { echo 'sample'; } ?>">
  <div class="row ballot-wrap-head sr-mute" aria-hidden="true">
    <div class="col-sm-6 col-md-4">
      <div class="row">
        <div class="col-xs-3">
          A
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-md-4 hidden-xs">
      <div class="row">
        <div class="col-xs-3">
          B
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-md-4 hidden-xs hidden-sm">
      <div class="row">
        <div class="col-xs-3">
          C
        </div>
      </div>
    </div>
  </div>

  <div class="row ballot-inst">
    <div class="col-md-9">
      <h2 class="h6">Ballot Marking Instructions:</h2>
      <ol>
        <li>Completely fill in the oval to the left of each selection of your choice as shown.</li>
        <li>For the purposes of this simulation election, at least one selection for every contest is required. If you do not wish to cast a vote for a particular contest, mark "No Selection."</li>
      </ol>
    </div>

    <div class="col-md-3">
      <img class="example" src="<?php echo Assets\asset_path('images/ballot-example.png'); ?>" srcset="<?php echo Assets\asset_path('images/ballot-example@2x.png'); ?> 2x" alt="" />
    </div>
  </div>

  <?php
  $ballot = CMB\get_voter_ballot_object();

  $output = "";

  // Get any submission errors
  if ( ( $error = $ballot->prop( 'submission_error' ) ) && is_wp_error( $error ) ) {
    $output .= '<h3>' . sprintf( 'There was an error in the submission: %s', '<strong>'. $error->get_error_message() .'</strong>' ) . '</h3>';
  }

  // Display metabox on page (changing save button text)
  $output .= cmb2_get_metabox_form( $ballot, 'fake-oject-id', array( 'save_button' => 'Cast Ballot' ) );

  echo $output;
  ?>

  <div class="ballot-footer sr-mute" aria-hidden="true">
    <div class="col-sm-6 col-md-4">
      <div class="row">
        <div class="col-xs-3">
          A
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-md-4 hidden-xs">
      <div class="row">
        <div class="col-xs-3">
          B
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-md-4 hidden-xs hidden-sm">
      <div class="row">
        <div class="col-xs-3">
          C
        </div>
      </div>
    </div>
  </div>
</div>

<?php if ($canvote === false) { ?>
  <script type="text/javascript">
    jQuery(document).ready(function($) {
      // Prevent submission when polls are closed
      $('#_cmb_voter_ballot_form').on('submit', function(e) {
        e.preventDefault();
      });
    });
  </script>
<?php } ?>
