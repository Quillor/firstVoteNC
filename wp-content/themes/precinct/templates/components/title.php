<?php

use Roots\Sage\Titles;
use Roots\Sage\Extras;

$precinct_name = get_bloginfo('name');
$precinct_id = substr( strrchr( get_bloginfo('url'), '/nc-' ), 4 );

if (get_post_type() == 'election' && !isset($_GET['edit']) && !isset($_GET['results']))
  return false;
?>

<header class="page-header">
  <div class="container">
    <?php if (is_singular(['post', 'election'])) { ?>
      <h1 class="entry-title">
        <?= Titles\title(); ?>
        <small>
          <?php echo $precinct_name; ?>
          &nbsp;&nbsp;&nbsp;&nbsp;
          <span class="h6">Precinct ID:</span> <?php echo $precinct_id; ?>
        </small>
      </h1>

      <?php if (isset($_GET['contest'])) {
        $race = $_GET['contest'];
        $contests = json_decode(get_option('precinct_contests'), true);
        $match = Extras\array_find_deep($contests, $race);
        ?>
        <h2>
        <?php echo $contests[$match[0][0][0]][$race]['title']; ?>
        <small><a class="btn btn-sm btn-gray btn-small" href="<?php echo remove_query_arg('contest'); ?>">Back to all results</a></small>
        </h2>
        <?php if (isset($contests[$match[0][0][0]][$race]['question'])) { ?>
          <div class="h2"><small><?php echo $contests[$match[0][0][0]][$race]['question']; ?></small></div>
        <?php } ?>
        <?php if (!empty($contests[$match[0][0][0]][$race]['number']) && !is_numeric($contests[$match[0][0][0]][$race]['number'])) { ?>
          <div class="h2"><small><?php echo $contests[$match[0][0][0]][$race]['number']; ?></small></div>
        <?php } ?>
      <?php } else {
        $type = $_GET['results'];
        ?>
        <ul class="nav nav-tabs">
          <li role="presentation" <?php if ($type == 'general') echo 'class="active"'; ?>><a href="<?php echo add_query_arg('results', 'general'); ?>">National/Statewide Contest Results</a></li>
          <li role="presentation" <?php if ($type == 'local') echo 'class="active"'; ?>><a href="<?php echo add_query_arg('results', 'local'); ?>">Local Contest Results</a></li>
          <li role="presentation" <?php if ($type == 'issues') echo 'class="active"'; ?>><a href="<?php echo add_query_arg('results', 'issues'); ?>">Issue-Based Question Results</a></li>
          <li role="presentation" <?php if ($type == 'participation') echo 'class="active"'; ?>><a href="<?php echo add_query_arg('results', 'participation'); ?>">Exit Poll Data</a></li>
          <li role="presentation" <?php if ($type == 'precincts') echo 'class="active"'; ?>><a href="<?php echo add_query_arg('results', 'precincts'); ?>">Other Precincts</a></li>
        </ul>
      <?php } ?>
    <?php } elseif (isset($_GET['add'])) { ?>
      <h1 class="entry-title">
        Add Simulation Election
        <small>
          <?php echo $precinct_name; ?>
          &nbsp;&nbsp;&nbsp;&nbsp;
          <span class="h6">Precinct ID:</span> <?php echo $precinct_id; ?>
        </small>
      </h1>
    <?php } elseif (is_page('lesson-plans')) { ?>
      <div class="row">
        <div class="col-md-7 col-centered">
          <h1 class="entry-title">Lesson Plans</h1>
        </div>
      </div>
    <?php } else { ?>
      <h1 class="entry-title">
        <?php echo $precinct_name; ?>
        <small><span class="h6">Precinct ID:</span> <?php echo $precinct_id; ?></small>
      </h1>
    <?php } ?>
  </div>
</header>
