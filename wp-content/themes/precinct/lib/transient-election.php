<?php

use Roots\Sage\Extras;

global $post;

// Determine which election to use
if (get_post_type() == 'election') {
  $election_id = get_the_id();
} elseif (get_post_type() == 'ballot') {
  $election_id = get_post_meta(get_the_id(), '_cmb_election_id', true);
}

// Includes
include(locate_template('/lib/transient-precinct.php'));
locate_template('/lib/google-auth.php', true, true);

// If there is an early voting date save_button
$early_voting = get_post_meta(get_the_id(), '_cmb_early_voting', true);
if (empty($early_voting)) {
 update_post_meta($election_id, '_cmb_voting_day', $master['voting_day']);
  update_post_meta($election_id, '_cmb_early_voting', $master['early_voting']);
}

// District data from Google
//if ( false === ($district_data = get_transient('districts_' . $election_id))) {
if ( true ) {
  $query = 'address=' . urlencode($master['address']);
  $result = get_civic_data('representatives', $query);
  $district_data = $result->divisions;
  set_transient('district_data_' . $election_id, $district_data, 7 * DAY_IN_SECONDS);
}

// // Election data from Google
// if ( false === ($election_data = get_transient('election_' . $election_id))) {
//   $query = 'address=' . urlencode($master['address']) . '&electionId=' . $master['election_id'] . '&officialOnly=true&returnAllAvailableData=true';
//   $election_data = get_civic_data('voterinfo', $query);
//   set_transient('election_data_' . $election_id, $election_data, 7 * DAY_IN_SECONDS);
// }

// Construct ballot
//if ( false === ($ballot_json = get_transient('ballot_' . $election_id))) {
if ( true ) {
  // $file = dirname(__FILE__) . '/north_carolina.xml';
  $file = $master['ballot_xml_file'];

  if (!empty($file)) {
    $ballot_xml = simplexml_load_file($file, 'CustomXMLElement');
  } else {
    echo 'Can\'t load file';
    $ballot_xml = false;
    return false;
  }

  // Loop through districts to get relevant contests
  $i = 0;
  $contest_data = [];
  foreach ($district_data as $ocd => $ocd_data) {
    // Override congressional district
    if (($pos = strpos($ocd, ':nc/cd:')) !== false) {
      if (!empty($districtNumber = $master['congressional_district'])) {
        $ocd = substr_replace($ocd, $districtNumber, $pos+7);
      }

    }
	
    $electoralDistrict = $ballot_xml->xpath("//ElectoralDistrict/ExternalIdentifiers/ExternalIdentifier/Value[text() = '$ocd']");
    if (count($electoralDistrict) > 0) {
      $contest_data[$i]['ocd'] = $ocd;

      // Find contests
      $electoralDistrictId = (string) $electoralDistrict[0]->get_parent_node()->get_parent_node()->get_parent_node()->attributes()['id'];
      $candidateContests = $ballot_xml->xpath("//CandidateContest/ElectoralDistrictId[text() = '$electoralDistrictId']");
      if (count($candidateContests) > 0) {
      $j = 0;
        foreach ($candidateContests as $candidateContest) {
          $contest_data[$i]['contests'][$j] = [
            'name' => (string) $candidateContest[0]->get_parent_node()->Name,
            'numberElected' => (string) $candidateContest[0]->get_parent_node()->NumberElected,
            'votesAllowed' => (string) $candidateContest[0]->get_parent_node()->VotesAllowed
          ];

          // District
          $districtNumber = $electoralDistrict[0]->get_parent_node()->get_parent_node()->get_parent_node()->Number;
          if (!empty($districtNumber)) {
            $contest_data[$i]['contests'][$j]['district'] = 'District ' . $districtNumber;
          }

          // Partisan?
          $officeId = (string) $candidateContest[0]->get_parent_node()->OfficeIds;
          $isPartisan = (string) $ballot_xml->xpath("//Office[@id = '$officeId']")[0]->IsPartisan;
          if (!empty ($isPartisan)) {
            $contest_data[$i]['contests'][$j]['partisan'] = $isPartisan;
          } else {
            $contest_data[$i]['contests'][$j]['partisan'] = 'false';
          }

          // Seat
          $seatName = $ballot_xml->xpath("//Office[@id = '$officeId']/ExternalIdentifiers/ExternalIdentifier/OtherType[text() = 'office-seat']");
          if (count($seatName) > 0) {
            $contest_data[$i]['contests'][$j]['seat'] = (string) $seatName[0]->get_parent_node()->Value;
          }

          // Find candidates
          $ballotSelections = explode(' ', (string) $candidateContest[0]->get_parent_node()->BallotSelectionIds);
          if (count($ballotSelections) > 0) {
            foreach ($ballotSelections as $ballotSelection) {
              $candidateId = (string) $ballot_xml->xpath("//CandidateSelection[@id = '$ballotSelection']")[0]->CandidateIds;
              $partyId = (string) $ballot_xml->xpath("//Candidate[@id = '$candidateId']")[0]->PartyId;
              $partyName = $ballot_xml->xpath("//Party[@id = '$partyId']/Name/Text");
              if (count($partyName) > 0 ) {
                $party = (string) $partyName[0];
              } else {
                $party = '';
              }
              $contest_data[$i]['contests'][$j]['candidates'][] = [
                'ballotName' => (string) addslashes($ballot_xml->xpath("//Candidate[@id = '$candidateId']")[0]->BallotName->Text),
                'party' => $party,
                'partyId' => $partyId
              ];

            }
          }

          $j++;
        }
      }
    }

    $i++;
  }
	
	/*
  // Sort array into order we need for ballot
  $ballot_order = [
    [
      'section' => 'Partisan Offices',
      'races' => [
        [
          'type' => 'single',
          'match' => true,
          'xml_title' => 'Custom Ballot',
          'ballot_title' => 'Custom Ballot',
          'partisan' => 'false'
        ]
      ]
    ],[
      'section' => 'Nonpartisan Offices',
      'races' => [
		[
          'type' => 'multiple',
          'match' => false,
          'xml_title' => 'Mayor',
		  'ballot_title' => 'District Mayor',
          'partisan' => 'false'
        ],[
          'type' => 'division',
          'match' => false,
          'ocd' => 'ocd-division/country:us/state:nc/county',
          'partisan' => 'false',
		  'votesAllowed' => '1',
          'order' => 'alpha'
        ],[
          'type' => 'division',
          'match' => false,
          'ocd' => 'ocd-division/country:us/state:nc/place',
          'partisan' => 'false',
		  'votesAllowed' => '1',
          'order' => 'alpha'
        ]
      ]
    ]
  ];
*/

  // Sort array into order we need for ballot
  $ballot_order = [
    [
      'section' => 'Partisan Offices',
      'races' => [
	   [
          'type' => 'single',
          'match' => true,
          'xml_title' => 'U.S. Senator',
          'ballot_title' => 'US Senate',
          'partisan' => 'true'
        ],[
          'type' => 'single',
          'match' => true,
          'xml_title' => 'U.S. Representative',
          'ballot_title' => 'US House of Representatives',
          'partisan' => 'true'
        ],[
          'type' => 'single',
          'match' => true,
          'xml_title' => 'NC Governor',
          'ballot_title' => 'NC Governor',
          'partisan' => 'true'
        ],[
          'type' => 'single',
          'match' => true,
          'xml_title' => 'NC Lt. Governor',
          'ballot_title' => 'NC Lieutenant Governor',
          'partisan' => 'true'
        ],[
          'type' => 'division',
          'match' => true,
          'ocd' => 'ocd-division/country:us/state:nc',
          'partisan' => 'true',
          'order' => 'alpha'
        ],[
          'type' => 'single',
          'match' => true,
          'xml_title' => 'NC State Senator',
          'ballot_title' => 'NC State Senate',
          'partisan' => 'true'
        ],[
          'type' => 'single',
          'match' => true,
          'xml_title' => 'NC State Representative',
          'ballot_title' => 'NC House of Representatives',
          'partisan' => 'true'
        ],[
          'type' => 'division',
          'match' => false,
          'ocd' => 'ocd-division/country:us/state:nc/county',
          'partisan' => 'true',
          'order' => 'alpha'
        ],[
          'type' => 'division',
          'match' => false,
          'ocd' => 'ocd-division/country:us/state:nc/place',
          'partisan' => 'true',
          'order' => 'alpha'
        ]
      ]
    ],[
      'section' => 'Nonpartisan Offices',
      'races' => [
        [
          'type' => 'multiple',
          'match' => false,
          'xml_title' => 'NC Supreme Court',
          'partisan' => 'false'
        ],[
          'type' => 'multiple',
          'match' => false,
          'xml_title' => 'NC Court of Appeals',
          'partisan' => 'false'
        ],[
          'type' => 'multiple',
          'match' => false,
          'xml_title' => 'District Court Judge',
          'partisan' => 'false'
        ]
      ]
    ]
  ];
  
   
  // Create ballot
  $j = 0;
  $k = 0;
  $ballot = [];
  foreach ($ballot_order as $ballot_section) {
    // Label this section
    $ballot[$j]['section'] = $ballot_section['section'];

    // Loop through contests and put on ballot
    foreach ($ballot_section['races'] as $ordered_contest) {
      // If this is a contest with data we just need to manually fill
      if ($ordered_contest['type'] == 'fill') {
        $ballot[$j]['races'][$k] = [
          'ballot_title' => $ordered_contest['ballot_title'],
          'district' => $ordered_contest['district'],
          'partisan' => $ordered_contest['partisan'],
          'votes_allowed' => $ordered_contest['votesAllowed'],
          'candidates' => $ordered_contest['candidates']
        ];
        $k++;
      // If this is a range of races from one ocd-division
      } elseif ($ordered_contest['type'] == 'division') {
        // Find corresponding ocd-division
        foreach ($contest_data as $contest) {
          if (($ordered_contest['match'] == TRUE && $contest['ocd'] == $ordered_contest['ocd']) || ($ordered_contest['match'] !== TRUE && stristr($contest['ocd'], $ordered_contest['ocd']))) {
            // Find corresponding contests
            $races = [];
            foreach ($contest['contests'] as $c) {
              if (!array_key_exists('on_ballot', $c) && ($c['partisan'] == $ordered_contest['partisan'])) {
                // If partyId is empty, fill it with z so it goes to the end
                foreach ($c['candidates'] as $key => $can) {
                  if (empty($can['partyId'])) $c['candidates'][$key]['partyId'] = 'z';
                }

                // Sort candidates by partyId
                usort($c['candidates'], function($a, $b) { return strcmp($a['partyId'], $b['partyId']); });

                // Set title
                $title = str_replace('NC State', 'NC', $c['name']);
                if (!empty($c['seat'])) {
                  $title .= '(' . $c['seat'] . ')';
                }

                // Add to ballot
                $races[] = [
                  'ballot_title' => $title,
                  'district' => $c['district'],
                  'partisan' => $c['partisan'],
                  'votes_allowed' => $c['votesAllowed'],
                  'candidates' => $c['candidates']
                ];
                // Indicate it was copied to ballot
                $c['on_ballot'] = true;
              }
            }

            // Sort by ballot_title
            usort($races, function($a, $b) { return strcmp($a['ballot_title'], $b['ballot_title']); });

            // Add to ballot
            foreach ($races as $race) {
              $ballot[$j]['races'][$k] = $race;
              $k++;
            }
          }
        }
      } else {
        if ($ordered_contest['type'] == 'single') {
          $ballot[$j]['races'][$k]['ballot_title'] = $ordered_contest['ballot_title'];
          // Find the corresponding contest info in the returned data
          $contests = Extras\array_find_deep($contest_data, $ordered_contest['xml_title']);
        } elseif ($ordered_contest['type'] == 'multiple') {
          // Find any corresponding contest info in the returned data
          $contests = Extras\array_find_deep($contest_data, $ordered_contest['xml_title'], false);
        }

        if (count($contests) > 0) {
          foreach ($contests[0][0] as $c) {
            if (!array_key_exists('on_ballot', $contest_data[$c[0]][$c[1]][$c[2]]) && ($contest_data[$c[0]][$c[1]][$c[2]]['partisan'] == $ordered_contest['partisan'])) {
                $candidates = $contest_data[$c[0]][$c[1]][$c[2]]['candidates'];

                // Sort by partyId
                usort($candidates, function($a, $b) { return strcmp($a['partyId'], $b['partyId']); });

                // Add to ballot
                if ($ordered_contest['type'] == 'multiple') {
                  $ballot[$j]['races'][$k]['ballot_title'] = $contest_data[$c[0]][$c[1]][$c[2]]['name'];
                }
                $ballot[$j]['races'][$k]['district'] = $contest_data[$c[0]][$c[1]][$c[2]]['district'];
                $ballot[$j]['races'][$k]['seat'] = $contest_data[$c[0]][$c[1]][$c[2]]['seat'];
                $ballot[$j]['races'][$k]['partisan'] = $contest_data[$c[0]][$c[1]][$c[2]]['partisan'];
                $ballot[$j]['races'][$k]['votes_allowed'] = $contest_data[$c[0]][$c[1]][$c[2]]['votesAllowed'];
                $ballot[$j]['races'][$k]['candidates'] = $candidates;

                // Indicate it was copied to ballot
                $contest_data[$c[0]][$c[1]][$c[2]]['on_ballot'] = true;

                $k++;
            }
          }
        }
      }
    }
    $j++;
  }

  $ballot_json = json_encode($ballot);
  

  set_transient('ballot_' . $election_id, $ballot_json, 7 * DAY_IN_SECONDS);
}


/**
 * Set custom post meta for contests
 */
if (get_post_type() == 'election') {
  update_post_meta($election_id, '_cmb_voting_day', $master['voting_day']);
  update_post_meta($election_id, '_cmb_ballot_json', $ballot_json);
}


// Query API for data
function get_civic_data($api, $query) {
  if (function_exists('google_api_key')) {
    $api_key = google_api_key();
    $query_string = 'https://www.googleapis.com/civicinfo/v2/' . $api . '?' . $query . '&key=' . $api_key;
    $api_get = wp_remote_get($query_string);

    if ( ! is_wp_error( $api_get ) ) {
      $result = json_decode($api_get['body']);
    } else {
      echo $api_get->get_error_message();
      $result = false;
    }
  } else {
    echo 'No Google API Key';
    $result = false;
  }

  return $result;
}

// Convert XML object to array
// http://stackoverflow.com/a/7778950
function xml2array($xml) {
  $arr = array();

  foreach ($xml as $element) {
    $tag = $element->getName();
    $e = get_object_vars($element);
    if (!empty($e)) {
      $arr[$tag] = $element instanceof CustomXMLElement ? xml2array($element) : $e;
    } else {
      $arr[$tag] = trim($element);
    }
  }

  return $arr;
}

// Get parent node in XML object
// http://stackoverflow.com/a/2174722
class CustomXMLElement extends SimpleXMLElement {
  public function get_parent_node() {
    return current($this->xpath('parent::*'));
  }
}
