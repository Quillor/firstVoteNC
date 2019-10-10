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
use Roots\Sage\Titles;
error_reporting(1);

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
/*
echo $votes_contest[0]->ID;
echo $votes_contest[0]->post_title;
echo $votes_contest[0]->post_content;
echo $votes_contest[0]->post_excerpt;
*/

$type = $_GET['results'];
	
$election_name = str_replace(' ', '_', $masterelection);
$election_name = strtolower($election_name);	

$nationwide = false;

if (isset($_GET['contest'])) {
  get_template_part('templates/layouts/results', 'contest');
} elseif ($_GET['results'] == 'precincts') {
  get_template_part('templates/layouts/results', 'precincts');
} elseif ($type == 'participation') {
  get_template_part('templates/layouts/results', 'participation');
} else {
  ?>

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

  <?php
  //$pattern = '/\\\\("[^"]+")/'; 
  $contests1 = html_entity_decode($votes_contest[0]->post_excerpt); 
  $contests2 = str_replace([' Jr','"Bob"', '"Buck"',  '"Tank"', ' "Toby" ', '"Rick"', '"Bill"', '"Buddy"', '"Randy"', '"Pat"', '"Al"', '"Phil"', '"Kathy"', '"Kirk"', '"Tony"'], 
  [', Jr', '\"Bob\"', '\"Buck\"',  '\"Tank\"', ' \"Toby\" ',  '\"Rick\"','\"Bill\"','\"Buddy\"', '\"Randy\"', '\"Pat\"', '\"Al\"', '\"Phil\"', '\"Kathy\"', '\"Kirk\"', '\"Tony\"'] , $contests1);
  $contests = json_decode($contests2, true );

 
  $uploads = wp_upload_dir();
  $uploads_global = network_site_url('wp-content/uploads');

  
  $results1 = ($votes_contest[0]->post_content);
  $results2 = str_replace(["\'", ' Jr', 'Charles Evans \"Democrat'], ["'", ', Jr', 'Charles Evans \"Democrat\"'], $results1);
  //$results2 = str_replace(["\'", ' Jr','\"Democrat\"','\"Republican\"'], ["'", ', Jr','',''], $results1);
  $results = json_decode($results2, true );

  //print_r($results2); 
  $statewide1 = file_get_contents($uploads_global . '/elections/election_results_'.$election_name.'.json');
  $statewide = json_decode( $statewide1, true);


  if( ($contests == null || $contests == '') || ($results[0] == null || $results[0] == '') ){
	echo '<h2 class="text-center">No results  Or Please recount the VOTE in the main page.</h2>';  
  }
  else{


  $races = array_keys($results[0]);
  $races_statewide = array_keys($statewide[0]);
  
  foreach ($races as $race) {


	  //echo $race; 
	  //echo substr($race, 0, 11) ; 
    if (substr($race, 0, 11) == '_cmb_ballot') {

      $match = Extras\array_find_deep($contests, $race);

      // Only show type of results for the tab we're on
      if ($type == 'general') {
        if (!in_array($race, $races_statewide) || isset($contests[$match[0][0][0]][$race]['question'])) {
		  continue;
        }else{
			$nationwide = true;
		}
      } elseif ($type == 'local') {
        if (in_array($race, $races_statewide) || isset($contests[$match[0][0][0]][$race]['question'])) {
          continue;
        }
      } elseif ($type == 'issues') {
        if (!isset($contests[$match[0][0][0]][$race]['question'])) {
          continue;
        }
      }

      $data = array_column($results, $race);
	
	  //print_r($data);
      $data_state = array_column($statewide, $race);
	  
	  // If data is JSON string, unserialize it
      if (FALSE !== unserialize($data[0])) {
		//print_r($data);
        $flat_data = array();
        foreach ($data as $multiple) {	
			//print_r($multiple); 
			$encoded = unserialize($multiple);
			//new
			$encoded = preg_replace('/(\'|&#0*39;)/', "\'", $encoded);	
			//print_r($encoded); 
			//$encoded = unserialize(html_entity_decode($encoded));	
			
			
			//$encoded = str_replace([' "Democrat"'], [''], $encoded);
			
			//$encoded = serialize(html_entity_decode($encoded));	
			//if(strpos($encoded, ' "Democrat"') !== false){
			//	$encoded = str_replace(['s:30', ' "Democrat"'], ['s:19',''], $encoded);
			//	}
			//print_r($encoded. ' <br />'); 
			//CJ
			
			$array = unserialize(html_entity_decode($encoded));	  
			//add CJ
			//print_r($encoded); 
			$array = str_replace(['"Democrat"', '"Republican"', '"Unaffiliated"', '"Libertarian"', '"Green"'], ['','','','',''], $array);
			//end
			$flat_data = array_merge(array_values($flat_data), array_values($array));

        }
        $data = $flat_data;
		//print_r($data . '<br />');
		//var_dump($data);
	
      }
	  else{
		  //added cj
		  //print_r($data);
		//$data = str_replace(['Charles Evans "Democrat'], ['Charles Evans "Democrat"'], $data);  
		$data = str_replace(['"Democrat"', '"Republican"', '"Unaffiliated"', '"Libertarian"', '"Green"', 'Charles Evans "Democrat'], ['','','','','','Charles Evans'], $data);  
		//print_r($data); 
	  }

      // If data is JSON string, unserialize it
      if (FALSE !== unserialize($data_state[0])) {
        $flat_data = array();
        foreach ($data_state as $multiple) {
          $encoded = unserialize($multiple);
          $array = unserialize(html_entity_decode($encoded));
          $flat_data = array_merge(array_values($flat_data), array_values($array));
        }
        $data_state = $flat_data;
      }

      // Total number of ballots cast
		//print_r($data);
      $total = count($data) - count(array_keys($data, NULL));
      $total_state = count($data_state) - count(array_keys($data_state, NULL));
	  //echo '<br />';
	 // print_r(count($data));

      // Set up arrays
      $count = array();
      $count_state = array();

      // Count number of votes per contestant
      if (isset($contests[$match[0][0][0]][$race]['candidates'])) {
        foreach ($contests[$match[0][0][0]][$race]['candidates'] as $candidate) {

			//
			$candidate['name'] = preg_replace('/(\'|&#0*39;)/', "\'", $candidate['name']);			
			$candidate['name'] = str_replace(',,',  ',',  $candidate['name']);			
			$data = str_replace(["\'", "  ", ',,'], ["'", " ", ','], $data);	
			$data = preg_replace('/(\'|&#0*39;)/', "\'", $data);	
			$data =  preg_replace('/[\[{\(].*[\]}\)]/U' , '', $data);
			//CJ 
			//print_r($candidate['name']. '<br/>');
			//print_r($data); 
			//print_r($data);
			
			$data = preg_replace('/\s+/', ' ', $data);
			$candidate['name'] = preg_replace('/\s+/', ' ', $candidate['name']);
			
          $tally = count(array_keys($data,$candidate['name']));
          //print_r(count(array_keys($data,$candidate['name'])));
          //print_r(array_keys($data,$candidate['name']));
          $tally_state = count(array_keys($data_state, $candidate['name']));
			
			
          // Precinct count
		  if($total == 0){
			  $count[] = array(
				'name' => str_replace('&', ' ', $candidate['name']),
				'party' => $candidate['party'],
				'count' => $tally,
				'percent' => 0
			  );
		  }else{
			 $count[] = array(
				'name' => str_replace('&', ' ', $candidate['name']),
				'party' => $candidate['party'],
				'count' => $tally,
				'percent' => round(($tally / $total) * 100, 2)
			  ); 
		  }
		  
          

          if ($type !== 'local' && in_array($race, $races_statewide)) {
            // Statewide count
            $count_state[] = array(
              'name' => $candidate['name'],
              'party' => $candidate['party'],
              'count' => $tally_state,
              'percent' => round(($tally_state / $total_state) * 100, 2)
            );
          }
        }
      } else {
        foreach ($contests[$match[0][0][0]][$race]['options'] as $option) {
          $tally = count(array_keys($data, $option));
          $tally_state = count(array_keys($data_state, $option));

          // Precinct count
          $count[] = array(
            'name' => $option,
            'count' => $tally,
            'percent' => round(($tally / $total) * 100, 2)
          );

          if ($type !== 'local' && in_array($race, $races_statewide)) {
            // Statewide count
            $count_state[] = array(
              'name' => $option,
              'count' => $tally_state,
              'percent' => round(($tally_state / $total_state) * 100, 2)
            );
          }
        }
      }

      if ($type !== 'issues') {
        // Total number of 'no selection' votes
        $tally_none = count(array_keys($data, 'none'));
        $tally_none_state = count(array_keys($data_state, 'none'));
		
		     // Precinct count
		  if($total == 0){
			$count[] = array(
				'name' => 'No Selection',
				'party' => 'no-selection',
				'count' => $tally_none,
				'percent' => 0
			);
		  }else{
				$count[] = array(
				  'name' => 'No Selection',
				  'party' => 'no-selection',
				  'count' => $tally_none,
				  'percent' => round(($tally_none / $total) * 100, 2)
				);
		  }
		
		

		
		 if($tally_none_state == 0 && $total_state == 0){
				// do nothing
			  $count_state[] = array(
			  'name' => 'No Selection',
			  'party' => 'no-selection',
			  'count' => $tally_none_state
			);
		 }else{
			$count_state[] = array(
			  'name' => 'No Selection',
			  'party' => 'no-selection',
			  'count' => $tally_none_state,
			  'percent' => round(($tally_none_state / $total_state) * 100, 2)
			);
		 }
       
      }

      if (isset($contests[$match[0][0][0]][$race]['number'])) {
        $number = $contests[$match[0][0][0]][$race]['number'];
      } else {
        $number = 1;
      }

      if (isset($contests[$match[0][0][0]][$race]['question'])) {
        $question = $contests[$match[0][0][0]][$race]['question'];
      }
	  
      ?>

      <div class="row">
        <div class="<?php if ($type == 'local' || !in_array($race, $races_statewide)) { echo 'col-sm-4'; } else { echo 'col-sm-12'; } ?>">
          <h2 class="h3">
            <?php
            echo $contests[$match[0][0][0]][$race]['title'] . ' ' . $contests[$match[0][0][0]][$race]['district'];
            if (isset($question)) {
              echo ' <small>' . $question . '</small>';
            }
            if (!empty($number) && !is_numeric($number)) {
              echo ' <small>' . $number . '</small>';
            } ?>
          </h2>
          <?php if (is_numeric($number) && $number > 1) { ?>
            <h3 class="h6"><?php echo $number; ?> Winners</h3>
          <?php } ?>
          <a class="btn btn-gray" href="<?php echo add_query_arg('contest', $race); ?>">Explore these results by exit poll</a>
        </div>

        <div class="<?php if ($type == 'local' || !in_array($race, $races_statewide)) { echo 'col-sm-8'; } else { echo 'col-sm-6 extra-bottom-margin'; } ?>">
          <div class="entry-content-asset">
            <div id="<?php echo $race; ?>" class="result-chart"></div>
          </div>
        </div>

        <?php if ($type !== 'local' && in_array($race, $races_statewide)) { ?>
          <div class="col-sm-6 extra-bottom-margin">
            <div class="entry-content-asset">
              <div id="state<?php echo $race; ?>" class="result-chart statewide"></div>
            </div>
          </div>
        <?php } ?>
      </div>

      <script type="text/javascript">
        new Highcharts.Chart({
          chart: { renderTo: '<?php echo $race; ?>', defaultSeriesType: 'bar' },
          credits: {enabled: false},
          title: { text: "<?php echo $contests[$match[0][0][0]][$race]['title'] . ' ' . $contests[$match[0][0][0]][$race]['district']; ?><br />(Precinct Results)", useHTML: true },
          <?php if (is_numeric($number) && $number > 1) { ?>
            subtitle: { text: "<?php echo $number; ?> Winners", useHTML: true },
          <?php } ?>
          <?php if (!empty($number) && !is_numeric($number)) { ?>
            subtitle: { text: "<?php echo $number; ?>", useHTML: true },
          <?php } ?>
          <?php if (isset($question)) { ?>
            subtitle: { text: "<?php echo $string = trim(preg_replace('/\s+/', ' ', $question)); ?>", useHTML: true },
          <?php } ?>
          xAxis: { type: 'category', tickWidth: 0, labels: { useHTML: true } },
          yAxis: { title: {enabled: false}, gridLineWidth: 0, labels: {enabled: false} },
          plotOptions: { bar: { dataLabels: { enabled: true, format: '{point.y:,.0f} votes ({point.percent:.2f}%)', inside: true, align: 'left', useHTML: true } } },
          legend: { enabled: false },
          tooltip: { enabled: false },
          series: [{ data: [<?php foreach ($count as $c) { ?>
              {
                name: '<?php echo str_replace(' & ', '<br />', $c['name']); ?><?php if (!empty($c['party'])) { echo '<br />(' . $c['party'] . ')'; } ?>',
                y: <?php echo $c['count']; ?>,
                className: '<?php if (isset($c['party'])) echo sanitize_title($c['party']); ?>',
                percent: <?php echo $c['percent']; ?>
                // animation: false
              },
            <?php } ?>]
          }]
        });

        <?php if ($type !== 'local' && in_array($race, $races_statewide)) { ?>
          new Highcharts.Chart({
            chart: { renderTo: 'state<?php echo $race; ?>', defaultSeriesType: 'bar' },
            credits: {enabled: false},
            title: { text: "<?php echo $contests[$match[0][0][0]][$race]['title'] . ' ' . $contests[$match[0][0][0]][$race]['district']; ?><br />(Statewide Results)", useHTML: true },
            <?php if (is_numeric($number) && $number > 1) { ?>
              subtitle: { text: "<?php echo $number; ?> Winners", useHTML: true },
            <?php } ?>
            <?php if (!empty($number) && !is_numeric($number)) { ?>
              subtitle: { text: "<?php echo $number; ?>", useHTML: true },
            <?php } ?>
            <?php if (isset($question)) { ?>
              subtitle: { text: "<?php echo $string = trim(preg_replace('/\s+/', ' ', $question)); ?>", useHTML: true },
            <?php } ?>
            xAxis: { type: 'category', tickWidth: 0, labels: { useHTML: true } },
            yAxis: { title: {enabled: false}, gridLineWidth: 0, labels: {enabled: false} },
            plotOptions: { bar: { dataLabels: { enabled: true, format: '{point.y:,.0f} votes ({point.percent:.2f}%)', inside: true, align: 'left', useHTML: true } } },
            legend: { enabled: false },
            tooltip: { enabled: false },
            series: [{ data: [<?php foreach ($count_state as $cs) { ?>
                {
                  name: '<?php echo str_replace(' & ', '<br />', $cs['name']); ?><?php if (!empty($cs['party'])) { echo '<br />(' . $cs['party'] . ')'; } ?>',
                  y: <?php echo $cs['count']; ?>,
                  className: '<?php if (isset($cs['party'])) echo sanitize_title($cs['party']); ?>',
                  percent: <?php echo $cs['percent']; ?>
                  // animation: false
                },
              <?php } ?>]
            }]
          });
        <?php } ?>
      </script>
      <?php
    }
  }
  	if($type != 'issues' && $type != 'participation' && $type != 'precincts'){
		if($nationwide == false && $type == 'general' ) {	
			echo '<h2 class="text-center">Not applicable for this election.</h2>';  
			?>
			<style>
				.container .row.extra-bottom-margin{display:none}
			</style>
	<?php	
		}
	}
}
}