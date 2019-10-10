<style>
.highcharts-label.highcharts-data-label.highcharts-data-label-color-0.highcharts-tracker {
    visibility: visible !important;
    opacity: 1 !important;
}
.highcharts-data-labels span {
    font-size: 13px!important;
    padding: 3px 5px;
    background: hsla(0,0%,100%,.5);
}
</style>
<?php

use Roots\Sage\Extras;

// Exit poll fields
include(locate_template('/lib/fields-exit-poll.php'));

// Which race?
$type = $_GET['results'];
$race = $_GET['contest'];

$masterelection = $_GET['election-option'];


  $wp_results = new WP_Query([
    'post_type' => 'election',
    'posts_per_page' => 1,
	'post_title_like' => $masterelection,
    'fields' => 'ids'
  ]);
$wp_result = $wp_results->posts;
//echo $wp_result[0];
wp_reset_postdata();

  $votes_contests = new WP_Query([
    'post_type' => 'votes_contest',
    'posts_per_page' => 1,
	'post_title_like' => $wp_result[0]
  ]);

$votes_contest = $votes_contests->posts; 

	
$election_name = str_replace(' ', '_', $masterelection);
$election_name = strtolower($election_name);

//$contests = json_decode(get_option('precinct_contests'), true);
 /* $pattern = '/\\\\("[^"]+")/'; 
  $contests1 = html_entity_decode($votes_contest[0]->post_excerpt); 
  $array_cont = preg_replace($pattern, '' ,$contests1);
  $contests = json_decode( $array_cont, true );*/
$pattern = '/\\\\("[^"]+")/'; 
  $contests1 = html_entity_decode($votes_contest[0]->post_excerpt);  
  $contests2 = str_replace(['"Bob"', '"Buck"',  '"Tank"', ' "Toby" ', '"Rick"', '"Bill"', '"Buddy"', '"Randy"', '"Pat"', '"Al"', '"Phil"', '"Kathy"', '"Kirk"', '"Tony"'],
  [ '\"Bob\"', '\"Buck\"',  '\"Tank\"', ' \"Toby\" ',  '\"Rick\"','\"Bill\"','\"Buddy\"', '\"Randy\"', '\"Pat\"', '\"Al\"', '\"Phil\"', '\"Kathy\"', '\"Kirk\"', '\"Tony\"'] , $contests1);
 
  $array_cont = preg_replace($pattern, '' ,$contests2);
  $contests = json_decode( $array_cont, true );

$match = Extras\array_find_deep($contests, $race);

// Results
$uploads = wp_upload_dir();
$uploads_global = network_site_url('wp-content/uploads');
/*
$results_json = wp_remote_get($uploads['baseurl'] . '/elections/precinct_results_'.$election_name.'.json');
if ( false === ( $results_json = get_option( 'precinct_votes' ) ) ) {
  $results_file = wp_remote_get($uploads['baseurl'] . '/elections/precinct_results_'.$election_name.'.json');
  $results_json = $results_file['body'];
}
$results = json_decode($results_json, true);
*/

  $results1 = ($votes_contest[0]->post_content);
  $array_cont = preg_replace($pattern, '',$results1);
  $results = json_decode( $array_cont, true );
  
  $statewide1 = file_get_contents($uploads_global . '/elections/election_results_'.$election_name.'.json');
  $array_cont = preg_replace($pattern, '',$statewide1);
  $statewide = json_decode( $array_cont, true);
  
$races = array_keys($results[0]);
$races_statewide = array_keys($statewide[0]);

if( ($contests == null || $contests == '') || ($results[0] == null || $results[0] == '') ){
	echo '<h2 class="text-center">No results yet Or Please recount the VOTE in the main page.</h2>';  
}
else{

	foreach ($ep_fields as $ep_field) {
	  // Results for this race
	  $data = array_column($results, $race);
	  $data_state = array_column($statewide, $race);

	  // If data is JSON string, unserialize it
	  if (FALSE !== (unserialize($data[0]))) {
		$flat_data = array();
		foreach ($data as $multiple) {
		  $encoded = unserialize($multiple);
		  $array = unserialize(html_entity_decode($encoded));
		  $flat_data = array_merge(array_values($flat_data), array_values($array));
		}
		$data = $flat_data;
	  }

	  // If data is JSON string, unserialize it
	  if (FALSE !== (unserialize($data_state[0]))) {
		$flat_data = array();
		foreach ($data_state as $multiple) {
		  $encoded = unserialize($multiple);
		  $array = unserialize(html_entity_decode($encoded));
		  $flat_data = array_merge(array_values($flat_data), array_values($array));
		}
		$data_state = $flat_data;
	  }

	  // Answers for this exit poll
	  $ep_data = array_column($results, $ep_field['id']);
	  $ep_data_state = array_column($statewide, $ep_field['id']);

	  // Clean html entities (quotations encoded weirdly)
	  foreach ($ep_data as &$clean) {
		$clean = preg_replace('/^don(.*)/i', 'Don\'t know', $clean);
	  }
	  foreach ($ep_data_state as &$clean) {
		$clean = preg_replace('/^don(.*)/i', 'Don\'t know', $clean);
	  }

	  // Total number of ballots cast
	  $total = count($data) - count(array_keys($data, NULL));
	  $total_state = count($data_state) - count(array_keys($data_state, NULL));

	  // Set up array tables
	  $ep_table = array();
	  $ep_table_state = array();

	  foreach ($ep_field['options'] as $ep_key => $ep_option) {
		// Table header for each exit poll answer
		$ep_table['headers'][] = $ep_option;
		$ep_table_state['headers'][] = $ep_option;

		// Total number of votes in this category
		  // In precinct
		  $ep_all = array_keys($ep_data, $ep_key);
		  $ep_total = count($ep_all);
		  $ep_table['total'][] = $ep_total;

		  // Statewide
		  $ep_all_state = array_keys($ep_data_state, $ep_key);
		  $ep_total_state = count($ep_all_state);
		  $ep_table_state['total'][] = $ep_total_state;

		// Total number of 'no selection' votes in this category
		  // In precinct
		  $none = array_keys($data, 'none');
		  $ep_none = count(array_intersect_key(array_flip($ep_all), array_flip($none)));
		  $ep_table['none'][] = array(
			'count' => $ep_none,
			'percent' => round(($ep_none / $ep_total) * 100, 2)
		  );

		  // Statewide
		  $none_state = array_keys($data_state, 'none');
		  $ep_none_state = count(array_intersect_key(array_flip($ep_all_state), array_flip($none_state)));
		  $ep_table_state['none'][] = array(
			'count' => $ep_none_state,
			'percent' => round(($ep_none_state / $ep_total_state) * 100, 2)
		  );
	  }

	  // Count number of votes per contestant
	  if (isset($contests[$match[0][0][0]][$race]['candidates'])) {
		foreach ($contests[$match[0][0][0]][$race]['candidates'] as $c_key => $candidate) {
		  // Get array keys for this contestant's votes
		  $keys = array_keys($data, $candidate['name']);
		  $keys_state = array_keys($data_state, $candidate['name']);

		  // Find all matching answers for this exit poll
		  $ep_answers = array_intersect_key($ep_data, array_flip($keys));
		  $ep_answers_state = array_intersect_key($ep_data_state, array_flip($keys_state));

		  // Row headers
		  $ep_table[$c_key] = [$candidate];
		  $ep_table_state[$c_key] = [$candidate];

		  foreach ($ep_field['options'] as $ep_key => $ep_option) {
			// Tally for each exit poll answer
			$tally = count(array_keys($ep_answers, $ep_key));
			$tally_state = count(array_keys($ep_answers_state, $ep_key));

			// Total number of votes in this category
			$ep_total = count(array_keys($ep_data, $ep_key));
			$ep_total_state = count(array_keys($ep_data_state, $ep_key));

			// Save to table to output at the end
			$ep_table[$c_key][] = array(
			  'party' => $candidate['party'],
			  'count' => $tally,
			  'percent' => round(($tally / $ep_total) * 100, 2)
			);

			$ep_table_state[$c_key][] = array(
			  'party' => $candidate['party'],
			  'count' => $tally_state,
			  'percent' => round(($tally_state / $ep_total_state) * 100, 2)
			);
		  }
		}
	  } else {
		foreach ($contests[$match[0][0][0]][$race]['options'] as $o_key => $option) {

		  // Get array keys for this options's votes
		  $keys = array_keys($data, $option);
		  $keys_state = array_keys($data_state, $option);

		  // Find all matching answers for this exit poll
		  $ep_answers = array_intersect_key($ep_data, array_flip($keys));
		  $ep_answers_state = array_intersect_key($ep_data_state, array_flip($keys_state));

		  // Row headers
		  $ep_table[$o_key][0]['name'] = $option;
		  $ep_table_state[$o_key][0]['name'] = $option;

		  foreach ($ep_field['options'] as $ep_key => $ep_option) {
			// Tally for each exit poll answer
			$tally = count(array_keys($ep_answers, $ep_key));
			$tally_state = count(array_keys($ep_answers_state, $ep_key));

			// Total number of votes in this category
			$ep_total = count(array_keys($ep_data, $ep_key));
			$ep_total_state = count(array_keys($ep_data_state, $ep_key));

			// Save to table to output at the end
			$ep_table[$o_key][] = array(
			  'count' => $tally,
			  'percent' => round(($tally / $ep_total) * 100, 2)
			);

			$ep_table_state[$o_key][] = array(
			  'count' => $tally_state,
			  'percent' => round(($tally_state / $ep_total_state) * 100, 2)
			);
		  }

		  // Remove "no selection" for these because it wasn't an option on ballot
		  unset($ep_table['none']);
		}
	  }


	// Set up table for iterating through columns
		// Precinct
		$none = $ep_table['none'];
		$footer = $ep_table['total'];
		$headers = $ep_table['headers'];
		unset($ep_table['headers']);
		unset($ep_table['none']);
		unset($ep_table['total']);

		// Statewide
		$none_state = $ep_table_state['none'];
		$footer_state = $ep_table_state['total'];
		$headers_state = $ep_table_state['headers'];
		unset($ep_table_state['headers']);
		unset($ep_table_state['none']);
		unset($ep_table_state['total']);


	// Remove K-5 and 6-8 for schools with no voters in those grades
		if ($ep_field['id'] == '_cmb_grade') {
		  $elementary = $footer[0];
		  $middle = $footer[1];
		  if ($elementary == 0 || $middle == 0) {
			foreach ($ep_table as $key => $row) {
			  if ($middle == 0) {
				// Precinct
				unset($none[1]);
				unset($footer[1]);
				unset($headers[1]);
				unset($ep_table[$key][2]);

				// Statewide
				unset($none_state[1]);
				unset($footer_state[1]);
				unset($headers_state[1]);
				unset($ep_table_state[$key][2]);
			  }
			  if ($elementary == 0) {
				// Precinct
				unset($none[0]);
				unset($footer[0]);
				unset($headers[0]);
				unset($ep_table[$key][1]);

				// Statewide
				unset($none_state[0]);
				unset($footer_state[0]);
				unset($headers_state[0]);
				unset($ep_table_state[$key][1]);
			  }
			  $ep_table[$key] = array_values($ep_table[$key]);
			  $ep_table_state[$key] = array_values($ep_table_state[$key]);
			}
		  }
		}


	// Get number of columns
	  $count_headers = count($headers);
	  $count_headers_state = count($headers_state);
	  if ($type !== 'local' && in_array($race, $races_statewide)) {
		$count_columns = $count_headers + $count_headers_state;
	  } else {
		$count_columns = $count_headers;
	  }


	// Highlight winners
		// Precinct
		$winner = array();
		for($i = 1; $i <= $count_headers; ++$i) {
		  $col = array_column(array_column($ep_table, $i), 'count');
		  if ($contests[$match[0][0][0]][$race]['number'] > 1) {
			// Multiple winners
			$sort = $col;
			arsort($sort);
			for ($j = 1; $j <= $contests[$match[0][0][0]][$race]['number']; $j++) {
			  $top = Extras\array_kshift($sort);
			  foreach ($top as $key => $value) {
				if ($value > 0) {
				  $winner[$i][$j] = $key;
				} else {
				  continue;
				}
			  }
			}
		  } else {
			// Winner is key of highest number
			$winner[$i] = array_keys($col, max($col));
		  }
		}

		// // Statewide
		$winner_state = array();
		for($i = 1; $i <= $count_headers_state; ++$i) {
		  // Winner is key of highest number
		  $col_state = array_column(array_column($ep_table_state, $i), 'count');
		  $winner_state[$i] = array_keys($col_state, max($col_state));
		}

	  // echo '<pre>';
	  // print_r($ep_table_state);
	  // print_r($winner_state);
	  // echo '</pre>';

	  ?>

	  <div class="row">
		  <h3>Results by <?php echo $ep_field['label']; ?></h3>
		  <h4 class="h6">
			<?php echo $contests[$match[0][0][0]][$race]['title']; ?>
			<?php if ($contests[$match[0][0][0]][$race]['number'] > 1) { ?>
			  (<?php echo $contests[$match[0][0][0]][$race]['number']; ?> Winners)
			<?php } ?>
		  </h4>

		<div class="table-responsive table-results">
		  <table class="table">
			<thead>
			  <?php if ($type !== 'local' && in_array($race, $races_statewide)) { ?>
				<tr>
				  <th scope="col" width="130px">&nbsp;</th>
				  <th scope="col" colspan="<?php echo ($count_columns/2); ?>" class="precinct">
					Precinct Results
				  </th>
				  <th scope="col" colspan="<?php echo ($count_columns/2); ?>" class="statewide">
					Statewide Results
				  </th>
				</tr>
			  <?php } ?>

			  <tr>
				<th scope="col" width="130px">&nbsp;</th>
				<?php
				// Precinct
				foreach ($headers as $header) { ?>
				  <th width="<?php echo 100/$count_columns; ?>%" class="precinct"><?php echo $header; ?></th>
				<?php }

				// Statewide
				if ($type !== 'local' && in_array($race, $races_statewide)) {
				  foreach ($headers_state as $header) { ?>
					<th width="<?php echo 100/$count_columns; ?>%" class="statewide"><?php echo $header; ?></th>
				  <?php }
				} ?>
			  </tr>
			</thead>

			<tbody>
			  <?php
			  foreach ($ep_table as $ep_key => $row) { ?>
				<tr>
				  <?php
				  // Precinct cells
				  foreach ($row as $k => $cell) {
					// If this is the first cell, it's a header for the row
					if ($k == 0) {
					  echo '<th scope="row">';
					} else {
					  if ($cell['count'] > 0 && in_array($ep_key, $winner[$k])) {
						echo '<td class="winner ' . sanitize_title($cell['party']) . ' precinct" >';
					  } else {
						echo '<td class="' . sanitize_title($cell['party']) . ' precinct" >';
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
				  }

				  // Statewide cells
				  if ($type !== 'local' && in_array($race, $races_statewide)) {
					foreach ($ep_table_state[$ep_key] as $k => $cell) {
					  // Skip first cell
					  if ($k == 0) {
						continue;
					  } else {
						if ($cell['count'] > 0 && in_array($ep_key, $winner_state[$k])) {
						  echo '<td class="winner ' . sanitize_title($cell['party']) . ' statewide" >';
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
				  <td class="precinct"><?php echo $blank['percent']; ?>% <small><?php echo $blank['count']; ?></small></td>
				<?php } ?>
				<?php if ($type !== 'local' && in_array($race, $races_statewide)) { foreach ($none_state as $blank_state) { ?>
				  <td class="statewide"><?php echo $blank_state['percent']; ?>% <small><?php echo $blank_state['count']; ?></small></td>
				<?php } } ?>
			  </tr>
			  <?php } ?>
			  <tr class="total">
				  <th scope="row">Total Votes</th>
				<?php foreach ($footer as $ep_total) { ?>
				  <td class="precinct"><?php echo $ep_total; ?></td>
				<?php } ?>
				<?php if ($type !== 'local' && in_array($race, $races_statewide)) { foreach ($footer_state as $ep_total_state) { ?>
				  <td class="statewide"><?php echo $ep_total_state; ?></td>
				<?php } } ?>
			  </tr>
			</tbody>
		  </table>
		</div>
	  </div>

	  <?php
	}
}

