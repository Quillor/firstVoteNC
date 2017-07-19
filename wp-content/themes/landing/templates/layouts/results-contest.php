<?php

// Exit poll fields
include(locate_template('/lib/fields-exit-poll.php'));

$uploads = wp_upload_dir();
$results = json_decode(file_get_contents($uploads['basedir'] . '/election_results.json'), true);
$contests = json_decode(file_get_contents($uploads['basedir'] . '/election_contests.json'), true);

// Which contest to show?
$race = $_GET['contest'];

foreach ($ep_fields as $ep_field) {
  // Results for this race
  $data = array_column($results, $race);

  // Answers for this exit poll
  $ep_data = array_column($results, $ep_field['id']);

  // Clean html entities (quotations encoded weirdly) - but html_entity_decode() isn't working on prod server. Also weird.
  foreach ($ep_data as &$clean) {
    $clean = preg_replace('/^don(.*)/i', 'Don\'t know', $clean);
  }

  // Total number of ballots cast
  $total = count($data) - count(array_keys($data, NULL));

  // Set up array table
  $ep_table = array();

  foreach ($ep_field['options'] as $ep_key => $ep_option) {
    // Table header for each exit poll answer
    $ep_table['headers'][] = $ep_option;

    // Total number of votes in this category
    $ep_all = array_keys($ep_data, $ep_key);
    $ep_total = count($ep_all);
    $ep_table['total'][] = $ep_total;

    // Total number of 'no selection' votes in this category
    $none = array_keys($data, 'none');
    $ep_none = count(array_intersect_key(array_flip($ep_all), array_flip($none)));
    $ep_table['none'][] = array(
      'count' => $ep_none,
      'percent' => round(($ep_none / $ep_total) * 100, 2)
    );
  }

  // Count number of votes per contestant
  if (isset($contests[$race]['candidates'])) {
    foreach ($contests[$race]['candidates'] as $c_key => $candidate) {
      // Get array keys for this contestant's votes
      $keys = array_keys($data, $candidate['name']);

      // Find all matching answers for this exit poll
      $ep_answers = array_intersect_key($ep_data, array_flip($keys));

      // Row headers
      $ep_table[$c_key] = [$candidate];

      foreach ($ep_field['options'] as $ep_key => $ep_option) {
        // Tally for each exit poll answer
        $tally = count(array_keys($ep_answers, $ep_key));

        // Total number of votes in this category
        $ep_total = count(array_keys($ep_data, $ep_key));

        // Save to table to output at the end
        $ep_table[$c_key][] = array(
          'party' => $candidate['party'],
          'count' => $tally,
          'percent' => round(($tally / $ep_total) * 100, 2)
        );
      }
    }
  } else {
    foreach ($contests[$race]['options'] as $o_key => $option) {
      // Get array keys for this options's votes
      $keys = array_keys($data, $option);

      // Find all matching answers for this exit poll
      $ep_answers = array_intersect_key($ep_data, array_flip($keys));

      // Row headers
      $ep_table[$o_key][0]['name'] = $option;

      foreach ($ep_field['options'] as $ep_key => $ep_option) {
        // Tally for each exit poll answer
        $tally = count(array_keys($ep_answers, $ep_key));

        // Total number of votes in this category
        $ep_total = count(array_keys($ep_data, $ep_key));

        // Save to table to output at the end
        $ep_table[$o_key][] = array(
          'count' => $tally,
          'percent' => round(($tally / $ep_total) * 100, 2)
        );
      }

      // Remove "no selection" for these because it wasn't an option on ballot
      unset($ep_table['none']);

    }
  }

  // echo '<pre>';
  // print_r($ep_table);
  // echo '</pre>';
  ?>

  <div class="row">
    <h3>Results by <?php echo $ep_field['label']; ?></h3>
    <h4 class="h6"><?php echo $contests[$race]['title']; ?></h4>

    <div class="table-responsive table-results">
      <table class="table">
        <thead>
          <tr>
            <th scope="col" width="130px">&nbsp;</th>
            <?php
            // Get number of columns so we can calculate width
            $count_columns = count($ep_table['headers']);
            $headers = $ep_table['headers'];
            unset($ep_table['headers']);
            foreach ($headers as $header) { ?>
              <th width="<?php echo 100/$count_columns; ?>%"><?php echo $header; ?></th>
            <?php } ?>
          </tr>
        </thead>

        <tbody>
          <?php
          // Set up table for iterating through columns
          $none = $ep_table['none'];
          $footer = $ep_table['total'];
          unset($ep_table['none']);
          unset($ep_table['total']);

          // Highlight winners
          $winner = '';
          for($i = 1; $i <= $count_columns; ++$i) {
            // Winner is key of highest number
            $col = array_column(array_column($ep_table, $i), 'count');
            $winner[$i] = array_keys($col, max($col));
          }

          foreach ($ep_table as $ep_key => $row) { ?>
            <tr>
              <?php
              foreach ($row as $k => $cell) {
                // If this is the first cell, it's a header for the row
                if ($k == 0) {
                  echo '<th scope="row">';
                } else {
                  if ($cell['count'] > 0 && in_array($ep_key, $winner[$k])) {
                    echo '<td class="winner ' . sanitize_title($cell['party']) . ' statewide">';
                  } else {
                    echo '<td class="' . sanitize_title($cell['party']) . ' statewide" >';
                  }
                }

                // Contents
                if (isset($cell['name'])) {
                  echo $cell['name'];
                  if (!empty($cell['party'])) echo "<br />({$cell['party']})";
                }
                if (isset($cell['count'])) echo "{$cell['percent']}% <small>{$cell['count']}</small>";

                // Close cell tag
                if ($i == 0) {
                  echo '</th>';
                } else {
                  echo '</td>';
                }
              } ?>
            </tr>
            <?php
          }
          if (!empty($none)) { ?>
          <tr>
              <th scope="row">No Selection</th>
            <?php foreach ($none as $blank) { ?>
              <td class="statewide"><?php echo $blank['percent']; ?>% <small><?php echo $blank['count']; ?></small></td>
            <?php } ?>
          </tr>
          <?php } ?>
          <tr class="total">
              <th scope="row">Total Votes</th>
            <?php foreach ($footer as $ep_total) { ?>
              <td><?php echo $ep_total; ?></td>
            <?php } ?>
          </tr>
        </tbody>
      </table>
    </div>

  </div>

<?php
}
