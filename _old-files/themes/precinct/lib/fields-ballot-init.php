<?php

// Determine which election to use
if (get_post_type() == 'election') {
  $election_id = get_the_id();
} elseif (get_post_type() == 'ballot') {
  $election_id = get_post_meta(get_the_id(), '_cmb_election_id', true);
}

// Election atts and properties
$ballot_data = json_decode(get_post_meta($election_id, '_cmb_ballot_json', true));
$included_races = get_post_meta($election_id, '_cmb_included_races', true);
$custom = get_post_meta($election_id, '_cmb_custom_contests', true);
$referenda = get_post_meta($election_id, '_cmb_included_referenda', true);
$issues = get_post_meta($election_id, '_cmb_custom_questions', true);

array_unshift($issues, [
    'title' => 'Life Skills',
    'question' => 'Do you think North Carolina\'s curriculum should include more life skill courses?'
  ],[
    'title' => 'Personal Data',
    'question' => 'In regards to the data on cell phones and personal computers, which is more important: public safety or privacy?',
    'options' => ['Public safety', 'Privacy']
  ]
);
