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
/*
array_unshift($issues, [
    'title' => 'Life Skills',
    'question' => 'Do you think North Carolina\'s curriculum should include more life skill courses?'
  ],[
    'title' => 'Personal Data',
    'question' => 'In regards to the data on cell phones and personal computers, which is more important: public safety or privacy?',
    'options' => ['Public safety', 'Privacy']
  ]
);

*/

array_unshift($issues,   [
    'title' => 'Issue #1',
    'question' => 'I am interested in internship opportunities connected to my high school.',
	'options' => ['Strongly Agree', 'Agree', 'Unsure', 'Disagree', 'Strongly Disagree']
  ], [
    'title' => 'Issue #2',
    'question' => 'Most of my classes are small enough to meet my academic needs.',
	'options' => ['Strongly Agree', 'Agree', 'Unsure', 'Disagree', 'Strongly Disagree']
  ], [
    'title' => 'Issue #3',
    'question' => 'There is at least one adult \'i.e., teacher, coach, counselor\' at my school I can talk to about personal issues.',
	'options' => ['Strongly Agree', 'Agree', 'Unsure', 'Disagree', 'Strongly Disagree']
  ], [
    'title' => 'Issue #4',
    'question' => 'There are enough job opportunities in my community that if I wanted to remain or return after high school and/or college graduation, I could.',
	'options' => ['Strongly Agree', 'Agree', 'Unsure', 'Disagree', 'Strongly Disagree']
  ], [
    'title' => 'Issue #5',
    'question' => 'Relationships between police and members of the community are positive.',
	'options' => ['Strongly Agree', 'Agree', 'Unsure', 'Disagree', 'Strongly Disagree']
  ], [
    'title' => 'Issue #6',
    'question' => 'Decisions about public monuments should be made at the state level, as law currently states, rather than by local governments and their communities.',
	'options' => ['Strongly Agree', 'Agree', 'Unsure', 'Disagree', 'Strongly Disagree']
  ], [
    'title' => 'Issue #7',
    'question' => 'There are satisfactory parks and recreation facilities in my community for young people.',
	'options' => ['Strongly Agree', 'Agree', 'Unsure', 'Disagree', 'Strongly Disagree']
  ], [
    'title' => 'Issue #8',
    'question' => 'Global warming will negatively impact our state within the next 50 years.',
	'options' => ['Strongly Agree', 'Agree', 'Unsure', 'Disagree', 'Strongly Disagree']
  ], [
    'title' => 'Issue #9',
    'question' => 'Local government has an impact on my daily life.',
	'options' => ['Strongly Agree', 'Agree', 'Unsure', 'Disagree', 'Strongly Disagree']
  ], [
    'title' => 'Issue #10',
    'question' => 'My education is preparing me for college or a job after I graduate.',
	'options' => ['Strongly Agree', 'Agree', 'Unsure', 'Disagree', 'Strongly Disagree']
  ]
);
