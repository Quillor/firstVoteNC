<script src="http://code.highcharts.com/highcharts.js"></script>
<script type="text/javascript" src="http://code.highcharts.com/modules/data.js"></script>
<script src="http://code.highcharts.com/modules/exporting.js"></script>
<script src="http://code.highcharts.com/modules/offline-exporting.js"></script>

<script type="text/javascript">
  Highcharts.setOptions({
    lang: {
      thousandsSep: ","
    }
  });
</script>

<?php
include(locate_template('/lib/fields-exit-poll.php'));

$contests = json_decode(get_option('precinct_contests'), true);

$uploads = wp_upload_dir();
$uploads_global = network_site_url('wp-content/uploads');
$results_json = wp_remote_get($uploads['baseurl'] . '/precinct_results.json');
if ( false === ( $results_json = get_option( 'precinct_votes' ) ) ) {
  $results_file = wp_remote_get($uploads['baseurl'] . '/precinct_results.json');
  $results_json = $results_file['body'];
}
$results = json_decode($results_json, true);
$statewide = json_decode(file_get_contents($uploads_global . '/election_results.json'), true);

$total = count($results);
$total_state = count($statewide) - count(array_keys(array_column($statewide, '_cmb_ballot_president-and-vice-president-of-the-united-states'), NULL));
?>

<div class="row">
  <div class="col-sm-12">
    <h2 class="h3">Total Ballots Cast</h2>
  </div>

  <div class="col-sm-6 extra-bottom-margin">
    <div class="panel text-center">
      <div class="h6">Your Precinct</div>
      <div class="h1"><?php echo number_format($total, 0, '.', ','); ?></div>
    </div>
  </div>

  <div class="col-sm-6 extra-bottom-margin">
    <div class="panel text-center">
      <div class="h6">Statewide</div>
      <div class="h1"><?php echo number_format($total_state, 0, '.', ','); ?></div>
    </div>
  </div>
</div>

<?php
foreach ($ep_fields as $ep_field) {
  // Answers for this exit poll
  $ep_data = array_column($results, $ep_field['id']);
  $ep_data_state = array_column($statewide, $ep_field['id']);

  $ep_total = count($ep_data) - count(array_keys($ep_data, NULL));
  $ep_total_state = count($ep_data_state) - count(array_keys($ep_data_state, NULL));

  // Clean html entities (quotations encoded weirdly)
  foreach ($ep_data as &$clean) {
    $clean = preg_replace('/^don(.*)/i', 'Don\'t know', $clean);
  }
  foreach ($ep_data_state as &$clean) {
    $clean = preg_replace('/^don(.*)/i', 'Don\'t know', $clean);
  }

  // Set up array tables
  $count = array();
  $count_state = array();

  foreach ($ep_field['options'] as $ep_key => $ep_option) {
    $tally = count(array_keys($ep_data, $ep_key));
    $tally_state = count(array_keys($ep_data_state, $ep_key));

    // Precinct count
    $count[] = array(
      'id' => $ep_key,
      'name' => addslashes($ep_option),
      'count' => $tally,
      'percent' => round(($tally / $ep_total) * 100, 2)
    );

    // Statewide count
    $count_state[] = array(
      'id' => $ep_key,
      'name' => addslashes($ep_option),
      'count' => $tally_state,
      'percent' => round(($tally_state / $ep_total_state) * 100, 2)
    );
  }

  // Remove K-5 and 6-8 for schools with no voters in those grades
  if ($ep_field['id'] == '_cmb_grade') {
    // Middle
    if ($count[1]['count'] == 0) {
      unset($count[1]);
    }
    // Elementary
    if ($count[0]['count'] == 0) {
      unset($count[0]);
    }
  }

  ?>
  <div class="row">
    <div class="col-sm-12">
      <h2 class="h3"><?php echo $ep_field['name']; ?></h2>
    </div>

    <div class="col-sm-6 extra-bottom-margin">
      <div class="entry-content-asset">
        <div id="<?php echo $ep_field['id']; ?>" class="result-chart"></div>
      </div>
    </div>

    <div class="col-sm-6 extra-bottom-margin">
      <div class="entry-content-asset">
        <div id="state<?php echo $ep_field['id']; ?>" class="result-chart statewide"></div>
      </div>
    </div>
  </div>

  <script type="text/javascript">
    new Highcharts.Chart({
      chart: { renderTo: '<?php echo $ep_field['id']; ?>', defaultSeriesType: 'pie' },
      credits: {enabled: false},
      title: { text: "<?php echo $ep_field['name']; ?><br />(Your Precinct)", useHTML: true },
      plotOptions: {
        pie: {
          dataLabels: {
            softConnector: true,
            enabled: true,
            format: '{point.name}:<br />{point.y:,.0f} ({point.percent:.2f}%)',
            useHTML: true,
            connectorColor: 'black'
          },
          size: '75%'
        }
      },
      // legend: { enabled: false },
      tooltip: { enabled: false },
      series: [{
        data: [<?php foreach ($count as $c) { ?>
          {
            name: '<?php echo $c['name']; ?>',
            y: <?php echo $c['count']; ?>,
            percent: <?php echo $c['percent']; ?>,
            className: '<?php echo $ep_field['id'] . '-' . sanitize_title($c['id']); ?>',
            dataLabels: {className: '<?php echo $ep_field['id'] . '-' . sanitize_title($c['id']); ?>'}
          },
        <?php } ?>]
      }]
    });

    new Highcharts.Chart({
      chart: { renderTo: 'state<?php echo $ep_field['id']; ?>', defaultSeriesType: 'pie' },
      credits: {enabled: false},
      title: { text: "<?php echo $ep_field['name']; ?><br />(Statewide)", useHTML: true },
      plotOptions: {
        pie: {
          dataLabels: {
            softConnector: true,
            enabled: true,
            format: '{point.name}:<br />{point.y:,.0f} ({point.percent:.2f}%)',
            useHTML: true,
            connectorColor: 'black'
          },
          size: '75%'
        }
      },
      // legend: { enabled: false },
      tooltip: { enabled: false },
      series: [{
        data: [<?php foreach ($count_state as $c) { ?>
          {
            name: '<?php echo $c['name']; ?>',
            y: <?php echo $c['count']; ?>,
            percent: <?php echo $c['percent']; ?>,
            className: '<?php echo $ep_field['id'] . '-' . sanitize_title($c['id']); ?>',
            dataLabels: {className: '<?php echo $ep_field['id'] . '-' . sanitize_title($c['id']); ?>'}
          },
        <?php } ?>]
      }]
    });
  </script>
  <?php
}
