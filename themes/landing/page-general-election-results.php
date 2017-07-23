<?php

use Roots\Sage\Setup;
use Roots\Sage\Titles;

get_template_part('templates/components/title', get_post_type());
?>

<div class="container">
  <?php
    if (isset($_GET['contest'])) {
      get_template_part('templates/layouts/results', 'contest');
    } elseif ($_GET['results'] == 'precincts') {
      get_template_part('templates/layouts/results', 'precincts');
    } elseif ($_GET['results'] == 'participation') {
      get_template_part('templates/layouts/results', 'participation');
    } else {
      get_template_part('templates/layouts/results');
    }
  ?>
</div><!-- /.container -->
