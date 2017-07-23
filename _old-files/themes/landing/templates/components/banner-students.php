<?php

use Roots\Sage\Assets;

?>
<section class="jumbotron">
  <div class="parallax-img hidden-xs" style="background-image:url('<?php echo Assets\asset_path('images/banner-precinct.jpg'); ?>')"></div>
  <div class="container">
    <img class="visible-xs-block" src="<?php echo Assets\asset_path('images/banner-xs.jpg'); ?>" alt="" />
    <h1>Welcome to the polls</h1>
    <p><em>Today is election day. Make your voice heard!</em></p>
    <p><a class="btn btn-primary btn-lg" href="<?php the_permalink(); ?>?lookup" role="button">Students, find your precinct</a></p>
  </div>
</section>

<?php if ( isset( $_GET['lookup'] ) ) { ?>
  <div class="modal fade" id="lookup" tabindex="-1" role="dialog" aria-label="Find your precinct">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="h3">Public and Charter High Schools</h4>
          <p>Each participating public or charter high school has a unique Precinct ID.</p>
          <form id="lookup_precinct" class="form-inline" method="post">
            <div class="form-group">
              <label for="precinctid">Precinct ID</label>
              <input type="text" class="form-control" name="precinctid" id="precinctid" />
            </div>
            <button type="submit" class="btn btn-default">Go</button>
          </form>
          <br />
        </div>

        <div class="modal-body">
          <h4 class="h3 ">All Other Schools</h4>
          <p>If you don't have a Precinct ID, you can still vote in the statewide First Vote NC election!</p>
          <p>
            <a href="http://www.firstvotenc.org/nc-915/" class="btn btn-default">Enter Here</a>
          </p>
        </div>
      </div>
    </div>
  </div>

  <script type="text/javascript">
    jQuery(document).ready(function($) {
      // Show on page load
      $('#lookup').modal('show');

      // Remove URL param when closing dialog
      $('#lookup').on('hide.bs.modal', function(e) {
        window.history.pushState(null, document.title, window.location.href.split('?')[0]);
      });

      // Redirect to precinct on form submit
      $('#lookup_precinct').on('submit', function(e) {
        var precinctid = $('#precinctid').val();
        if (precinctid !== '') {
          window.location.href = "http://" + window.location.hostname + "/nc-" + precinctid;
        }
        e.preventDefault();
      });
    });
  </script>
<?php } ?>
