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

$uploads = wp_upload_dir();
$results = json_decode(file_get_contents($uploads['basedir'] . '/election_results.json'), true);
$contests = json_decode(file_get_contents($uploads['basedir'] . '/election_contests.json'), true);

$total = count($results) - count(array_keys(array_column($results, '_cmb_ballot_president-and-vice-president-of-the-united-states'), NULL));

?>

<div class="row extra-bottom-margin">
  <div class="col-sm-4">
    <h2 class="h3">Total Ballots Cast</h2>
  </div>

  <div class="col-sm-8">
    <div class="panel text-center">
      <div class="h1"><?php echo number_format($total, 0, '.', ','); ?></div>
    </div>
  </div>
</div>

<?php
foreach ($ep_fields as $ep_field) {
  // Answers for this exit poll
  $ep_data = array_column($results, $ep_field['id']);

  $ep_total = count($ep_data) - count(array_keys($ep_data, NULL));

  // Clean html entities (quotations encoded weirdly) - but html_entity_decode() isn't working on prod server. Also weird.
  foreach ($ep_data as &$clean) {
    $clean = preg_replace('/^don(.*)/i', 'Don\'t know', $clean);
  }

  // Set up array tables
  $count = array();

  foreach ($ep_field['options'] as $ep_key => $ep_option) {
    $tally = count(array_keys($ep_data, $ep_key));

    // Precinct count
    $count[] = array(
      'id' => $ep_key,
      'name' => addslashes($ep_option),
      'count' => $tally,
      'percent' => round(($tally / $ep_total) * 100, 2)
    );
  }
  ?>

  <div class="row">
    <div class="col-sm-4">
      <h2 class="h3"><?php echo $ep_field['name']; ?></h2>
    </div>

    <div class="col-sm-8">
      <div class="entry-content-asset">
        <div id="<?php echo $ep_field['id']; ?>" class="result-chart"></div>
      </div>
    </div>
  </div>

  <script type="text/javascript">
    new Highcharts.Chart({
      chart: { renderTo: '<?php echo $ep_field['id']; ?>', defaultSeriesType: 'pie' },
      credits: {enabled: false},
      title: { text: "<?php echo $ep_field['name']; ?>", useHTML: true },
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
  </script>
  <?php
}
