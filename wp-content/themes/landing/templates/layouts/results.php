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
$election = $_GET['election-option'];
$election_name = str_replace(' ', '_', $election);
$election_name = strtolower($election_name);

$uploads = wp_upload_dir();
$results = json_decode(file_get_contents($uploads['basedir'] . '/elections/election_results_'.$election_name.'.json'), true);
$contests = json_decode(file_get_contents($uploads['basedir'] . '/elections/election_contests_'.$election_name.'.json'), true);

$type = $_GET['results'];

if(!isset($_GET['results'])){
	$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$newURL =  $actual_link . '&results=precincts';
	header('Location: '.$newURL);
	die();
}

 //echo '<pre>';
//print_r($results[0]);
 //echo '</pre>';
$partisan = false;
$non_partisan = false;
$non_partisan_tally = '';



 
if( ($contests == null || $contests == ' ') || ($results[0] == null || $results[0] == ' ') ){
	echo '<h2 class="text-center">No results yet Or Please recount the VOTE in the main page.</h2>';  
}
else{ 

	$races = array_keys($results[0]);

	foreach ($races as $race) {
	  if (substr($race, 0, 11) == '_cmb_ballot') {
		$data = array_column($results, $race);
		// Total number of ballots cast
		$total = count($data) - count(array_keys($data, NULL));

		$counts = array();
		

		

		// Only show type of results for the tab we're on
		if ($type == 'nonpartisan') {		
		  if (isset($contests[$race]['candidates'][0]['party']) || isset($contests[$race]['question'])) {
			continue;
		  }
		} elseif ($type == 'issues') {
		  if (!isset($contests[$race]['question'])) {
			continue;
		  }
		} else {
		  if (!isset($contests[$race]['candidates'][0]['party']) || isset($contests[$race]['question'])) {
			continue;
		  }else{
			  $partisan = true;
		  }
		}
	
		// Count number of votes per contestant
		if (isset($contests[$race]['candidates'])) {
		  foreach ($contests[$race]['candidates'] as $candidate) {
			$tally = count(array_keys($data, $candidate['name']));
			$counts[] = array(
			  'name' => $candidate['name'],
			  'party' => $candidate['party'],
			  'count' => $tally,
			  'percent' => round(($tally / $total) * 100, 2)
			);
			$non_partisan_tally =  $tally;	
		  }
		} else {
			if($contests[$race]['options'] == null){
				//do nothing
			}else{
			  $tally = '';
			  foreach ($contests[$race]['options'] as $option) {
				$tally = count(array_keys($data, $option));
				$counts[] = array(
				  'name' => $option,
				  'count' => $tally,
				  'percent' => round(($tally / $total) * 100, 2)
				);
			  }
				if($tally != null || $tally != ''){
				  $non_partisan = true;
			  }
			}
		
		}

		if ($type !== 'issues') {
		  // Total number of 'no selection' votes
		  $tally_none = count(array_keys($data, 'none'));
		  
		  if($total == 0){		  
			  $counts[] = array(
				'name' => 'No Selection',
				'party' => 'no-selection',
				'count' => $tally_none,
				'percent' => 0
			  );
		  }else{		  
			  $counts[] = array(
				'name' => 'No Selection',
				'party' => 'no-selection',
				'count' => $tally_none,
				'percent' => round(($tally_none / $total) * 100, 2)
			  );
		  }
		}
		?>

		<div class="row extra-bottom-margin">
		  <div class="col-sm-4">
			<h2 class="h3"><?php echo $contests[$race]['title']; ?></h2>
			<?php if (!empty($contests[$race]['question'])) { ?>
			  <h3 class="h4"><?php echo $contests[$race]['question']; ?></h3>
			<?php } ?>
			<a class="btn btn-gray" href="<?php echo add_query_arg('contest', $race); ?>">Explore these results by exit poll</a>
		  </div>

		  <div class="col-sm-8">
			<div id="<?php echo $race; ?>" class="result-chart"></div>
		  </div>
		</div>

		<script type="text/javascript">
		  new Highcharts.Chart({
			chart: { renderTo: '<?php echo $race; ?>', defaultSeriesType: 'bar' },
			credits: {enabled: false},
			title: { text: "<?php echo $contests[$race]['title']; ?><br />", useHTML: true },
			<?php if (isset($contests[$race]['question'])) { ?>
			  subtitle: { text: "<?php echo $contests[$race]['question']; ?>", useHTML: true },
			<?php } ?>
			xAxis: { type: 'category', tickWidth: 0, labels: { useHTML: true } },
			yAxis: { title: {enabled: false}, gridLineWidth: 0, labels: {enabled: false} },
			plotOptions: { bar: { dataLabels: { enabled: true, format: '{point.y:,.0f} votes ({point.percent:.2f}%)', inside: true, align: 'left', useHTML: true } } },
			legend: { enabled: false },
			tooltip: { enabled: false },
			series: [{ data: [<?php foreach ($counts as $count) { ?>
				{
				  name: '<?php echo str_replace(' & ', '<br />', $count['name']); ?><?php if (!empty($count['party'])) { echo '<br />(' . $count['party'] . ')'; } ?>',
				  y: <?php echo $count['count']; ?>,
				  className: '<?php if (isset($count['party'])) echo sanitize_title($count['party']); ?>',
				  percent: <?php echo $count['percent']; ?>
				  // animation: false
				},
			  <?php } ?>]
			}]
		  });
		</script>
		<?php
	  }
	}
	
	
	if($type != 'issues' && $type != 'participation' && $type != 'precincts'){
		if($partisan == false && $type == 'partisan' ) {
			if ($_GET['election-option'] == '2018 General Election' ) { 
				echo "
					<h2 class='text-center'>No national/statewide partisan results for this election.</h2>
					<p class='text-center lead'>					
						Check judicial and nonpartisan contest results, referenda and issue-based question, and exit-poll data links for state-wide results. <br/>
						Check link for your individual school's results.
					</p>
				";
			}
			else{
				echo "
					<h2 class='text-center'>No partisan results for this election.</h2>
					<p class='text-center lead'>					
						Check issue-based question and exit-poll data links for state-wide results. <br/>
						Check link for your individual school's results.
					</p>
				";
			}?>
			
			<style>
				.container .row.extra-bottom-margin{display:none}
			</style>
	<?php	
		}
		if(($non_partisan_tally == '' && $type == 'nonpartisan') && $_GET['election-option'] != '2018 General Election' ) {	
			echo "	
				<h2 class='text-center'>No non-partisan results for this election.</h2>
				<p class='text-center lead'>					
					Check issue-based question and exit-poll data links for state-wide results. <br/>
					Check link for your individual school's results.
				</p>
			";?>
			<style>
				.container .row.extra-bottom-margin{display:none}
			</style>
	<?php	
		}
		
	}
}
