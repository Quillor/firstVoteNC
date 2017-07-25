<?php

use Roots\Sage\Titles;

$image_id = get_post_thumbnail_id();
$featured_image_lg = wp_get_attachment_image_src($image_id, 'large');
$election = $_GET['election-option'];
$election_name = str_replace(' ', '_', $election);
$election_name = strtolower($election_name);
?>

<header class="page-header photo-overlay" style="background-image: url('<?php echo $featured_image_lg[0]; ?>')">
  <div class="article-title-overlay">
    <div class="container">
      <div class="row">
        <div class="<?php if (!is_page('2016-general-election-results')) { echo 'col-md-8 col-centered'; } ?>">
          <!-- <h1 class="entry-title"><?//= Titles\title(); ?></h1>-->
          <h1 class="entry-title"><?php echo $election; ?></h1>

          <?php
          if (is_page('2016-general-election-results')) {
            if (isset($_GET['contest'])) {
              $race = $_GET['contest'];
              $uploads = wp_upload_dir();
              $contests = json_decode(file_get_contents($uploads['basedir'] . '/election_contests.json'), true);
              ?>
              <h2>
              <?php echo $contests[$race]['title']; ?>
              <a class="btn btn-sm btn-gray btn-small" href="<?php echo remove_query_arg('contest'); ?>">Back to all results</a>
              </h2>
              <?php if (isset($contests[$race]['question'])) { ?>
                <div class="h2"><small><?php echo $contests[$race]['question']; ?></small></div>
              <?php } ?>
            <?php } else {
              $type = $_GET['results'];
              ?>
              <ul class="nav nav-tabs">
                <li role="presentation" <?php if (empty($type)) echo 'class="active"'; ?>><a href="<?php echo remove_query_arg('results'); ?>">Partisan Contest Results</a></li>
                <li role="presentation" <?php if ($type == 'nonpartisan') echo 'class="active"'; ?>><a href="<?php echo add_query_arg('results', 'nonpartisan'); ?>">Nonpartisan Contest Results</a></li>
                <li role="presentation" <?php if ($type == 'issues') echo 'class="active"'; ?>><a href="<?php echo add_query_arg('results', 'issues'); ?>">Issue-Based Question Results</a></li>
                <li role="presentation" <?php if ($type == 'participation') echo 'class="active"'; ?>><a href="<?php echo add_query_arg('results', 'participation'); ?>">Exit Poll Data</a></li>
                <li role="presentation" <?php if ($type == 'precincts') echo 'class="active"'; ?>><a href="<?php echo add_query_arg('results', 'precincts'); ?>">Precinct Results</a></li>
              </ul>
            <?php }
          } elseif (is_page('general-election-results')) {
            if (isset($_GET['contest'])) {
              $race = $_GET['contest'];
              $uploads = wp_upload_dir();
              $contests = json_decode(file_get_contents($uploads['basedir'] . '/elections/election_contests_'.$election_name.'.json'), true);
              ?>
              <h2>
              <?php echo $contests[$race]['title']; ?>
              <a class="btn btn-sm btn-gray btn-small" href="<?php echo remove_query_arg('contest'); ?>">Back to all results</a>
              </h2>
              <?php if (isset($contests[$race]['question'])) { ?>
                <div class="h2"><small><?php echo $contests[$race]['question']; ?></small></div>
              <?php } ?>
            <?php } else {
              $type = $_GET['results'];
              ?>
              <ul class="nav nav-tabs">
                <li role="presentation" <?php if (empty($type)) echo 'class="active"'; ?>><a href="<?php echo remove_query_arg('results'); ?>">Partisan Contest Results</a></li>
                <li role="presentation" <?php if ($type == 'nonpartisan') echo 'class="active"'; ?>><a href="<?php echo add_query_arg('results', 'nonpartisan'); ?>">Nonpartisan Contest Results</a></li>
                <li role="presentation" <?php if ($type == 'issues') echo 'class="active"'; ?>><a href="<?php echo add_query_arg('results', 'issues'); ?>">Issue-Based Question Results</a></li>
                <li role="presentation" <?php if ($type == 'participation') echo 'class="active"'; ?>><a href="<?php echo add_query_arg('results', 'participation'); ?>">Exit Poll Data</a></li>
                <li role="presentation" <?php if ($type == 'precincts') echo 'class="active"'; ?>><a href="<?php echo add_query_arg('results', 'precincts'); ?>">Precinct Results</a></li>
              </ul>
            <?php }
          } ?>
        </div>
      </div>
    </div>
  </div>
</header>
