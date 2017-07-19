<?php

use Roots\Sage\Assets;

?>

<div class="modal fade" id="iVoted" tabindex="-1" role="dialog" aria-label="I Voted Today!">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h2 class="h3 text-center">Thank you for voting!</h2>
        <p class="text-center">
          <img src="<?php echo Assets\asset_path('images/sticker.png'); ?>" srcset="<?php echo Assets\asset_path('images/sticker@2x.png'); ?> 2x" alt="I Voted Today! Sticker" />
        </p>
        <p class="text-center">
          Your ballot has been cast and results will be ready November 9th!
        </p>
        <p class="text-center">
          <button type="button" class="btn btn-gray" data-dismiss="modal">Return to Precinct</button>
        </p>
      </div>

      <div class="modal-body">
        <h2 class="h3 text-center">Are you at least 17 years old?</h2>
        <p>If so, you are invited to sign up below for information about registering to vote, election reminders, and more &mdash; straight to your phone or email!</p>
        <p class="text-center">
          <a href="https://firstvotenc.turbovote.org/name" target="_blank" class="btn btn-default btn-lg">Sign up for TurboVote</a>
        </p>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  jQuery(document).ready(function($) {
    // Show on page load
    $('#iVoted').modal('show');

    // Remove URL param when closing dialog
    $('#iVoted').on('hide.bs.modal', function(e) {
      window.history.pushState(null, document.title, window.location.href.split('?')[0]);
    });
  });
</script>
