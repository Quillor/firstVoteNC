<script src="https://code.highcharts.com/highcharts.js"></script>
<script type="text/javascript" src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/offline-exporting.js"></script>

<script type="text/javascript">
  Highcharts.setOptions({
    lang: {
      thousandsSep: ","
    }
  });
</script>
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
include(locate_template('/lib/fields-exit-poll.php'));
error_reporting(0);
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

//$contests = json_decode(get_option('precinct_contests'), true);
  $pattern = '/\\\\("[^"]+")/'; 
  $contests1 = html_entity_decode($votes_contest[0]->post_excerpt); 
  $contests2 = str_replace(' Jr', ', Jr' , $contests1);
  $contests = json_decode($contests2, true );
  
$election_name = str_replace(' ', '_', $masterelection);
$election_name = strtolower($election_name);


$uploads = wp_upload_dir();
$uploads_global = network_site_url('wp-content/uploads');
$results_json = wp_remote_get($uploads['baseurl'] . '/elections/precinct_results_'.$election_name.'.json');


   /*$results1 = ($votes_contest[0]->post_content);
  $array_cont = preg_replace($pattern, '',$results1);
  $results = json_decode( $results1, true );
  
 $statewide1 = file_get_contents($uploads_global . '/elections/election_results_'.$election_name.'.json');
  $array_cont = preg_replace($pattern, '',$statewide1);
  $statewide = json_decode( $array_cont, true); */
  
  $results1 = ($votes_contest[0]->post_content);
  $results2 = str_replace(["\'", ' Jr'], ["'", ', Jr'], $results1);
  $results = json_decode($results2, true );
  
  $statewide1 = file_get_contents($uploads_global . '/elections/election_results_'.$election_name.'.json');
  $statewide = json_decode( $statewide1, true);

$total = count($results);
//$total_state = count($statewide) - count(array_keys(array_column($statewide, '_cmb_ballot_president-and-vice-president-of-the-united-states'), NULL));
$total_state = count($statewide) - count(array_keys(array_column($statewide, 'blog_id'), NULL));
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
	if($total == 0){
		$count[] = array(
		  'id' => $ep_key,
		  'name' => addslashes($ep_option),
		  'count' => $tally,
		  'percent' => 0
		);
		
	}else{
		$count[] = array(
		  'id' => $ep_key,
		  'name' => addslashes($ep_option),
		  'count' => $tally,
		  'percent' => round(($tally / $ep_total) * 100, 2)
		);
	}
   

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
  <div class="row pie-counts">
    <div class="col-sm-12">
      <h2 class="h3"><?php echo $ep_field['name']; ?></h2>
    </div>

    <div class="col-sm-6 extra-bottom-margin pie-red-block">
      <div class="entry-content-asset">
        <div id="<?php echo $ep_field['id']; ?>" class="result-chart"></div>
      </div>
    </div>

    <div class="col-sm-6 extra-bottom-margin pie-blue-block">
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
		  allowPointSelect: true,
		  cursor: 'pointer',
          dataLabels: {
            //softConnector: true,
            enabled: true,
            format: '{point.name}:<br />{point.y:,.0f} ({point.percent:.2f}%)',
			style: {
                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                } 
            //useHTML: true,
            //connectorColor: 'black'
          },
          size: '50%'
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
		  allowPointSelect: true,
		  cursor: 'pointer',
          dataLabels: {
            softConnector: true,
            enabled: true,
            format: '{point.name}:<br />{point.y:,.0f} ({point.percent:.2f}%)',
            useHTML: true,
            connectorColor: 'black'
          },
          size: '50%'
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
