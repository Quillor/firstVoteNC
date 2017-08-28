<?php

use Roots\Sage\Assets;

?>
<section class="jumbotron">
  <div class="parallax-img hidden-xs" style="background-image:url('<?php echo Assets\asset_path('images/banner-flag.jpg'); ?>')"></div>
  <div class="container text-center">
    <img class="visible-xs-block" src="<?php echo Assets\asset_path('images/banner-flag-xs.jpg'); ?>" alt="" />
    <h1>The results are in!</h1>
    <p><em>See how North Carolina's youth voted in the general election.</em></p>
    <p><a class="btn btn-primary btn-lg" href="/general-election-results/" role="button" class="Explore results!">Explore results</a></p>
  </div>
</section>
