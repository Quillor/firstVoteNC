<?php
//landing theme

// Determine which election to use
if (get_post_type() == 'election') {
  $election_id = get_the_id();
} elseif (get_post_type() == 'ballot') {
  $election_id = get_post_meta(get_the_id(), '_cmb_election_id', true);
}

// Election atts and properties
$ballot_data = json_decode(stripslashes(get_post_meta($election_id, '_cmb_ballot_json', true)));
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
array_unshift($referenda,   [
    'title' => 'Constitutional Amendment #1',
    'question' => 'Constitutional amendment protecting the right of the people to hunt, fish, and harvest wildlife.',
  'options' => ['For', 'Against']
  ], [
    'title' => 'Constitutional Amendment #2',
    'question' => 'Constitutional amendment to strengthen protections for victims of crime; to establish certain absolute basic rights for victims; and to ensure the enforcement of these rights.',
  'options' => ['For', 'Against']
  ], [
    'title' => 'Constitutional Amendment #3',
    'question' => "Constitutional amendment to reduce the income tax rate in North Carolina to a maximum allowable rate of seven percent '7%'.",
  'options' => ['For', 'Against']
   ], [
    'title' => 'Constitutional Amendment #4',
    'question' => 'Constitutional amendment to require voters to provide photo identification before voting in person.',
  'options' => ['For', 'Against']
  ], [
    'title' => 'Constitutional Amendment #5',
    'question' => 'Constitutional amendment to change the process for filling judicial vacancies that occur between judicial elections from a process in which the Governor has sole appointment power to a process in which the people of the State nominate individuals to fill vacancies by way of a commission comprised of appointees made by the judicial, executive, and legislative branches charged with making recommendations to the legislature as to which nominees are deemed qualified; then the legislature will recommend at least two nominees to the Governor via legislative action not subject to gubernatorial veto; and the Governor will appoint judges from among these nominees.',
  'options' => ['For', 'Against']
  ], [
    'title' => 'Constitutional Amendment #6',
    'question' => 'Constitutional amendment to establish an eight-member Bipartisan Board of Ethics and Elections Enforcement in the Constitution to administer ethics and elections law.',
  'options' => ['For', 'Against']
  ]
);
array_unshift($issues,   [
    'title' => 'Issue #1',
    'question' => 'I believe the news I read on social media sites is accurate.',
  'options' => ['Strongly Agree', 'Agree', 'Unsure', 'Disagree', 'Strongly Disagree']
  ], [
    'title' => 'Issue #2',
    'question' => 'What grade would you give yourself on your knowledge about personal finance ex. budgeting, banking, credit cards?',
  'options' => ['A', 'B', 'C', 'D','F']
  ], [
    'title' => 'Issue #3',
    'question' => 'What has been the biggest influence on how you learn about managing your money?',
  'options' => ['Parents', 'Friends or Classmates', 'Teachers in a classroom setting', 'Internet Sources ex. blogs, articles, etc.','Social Media','Other']
  ], [
    'title' => 'Issue #4',
    'question' => 'After graduation, what do you plan to do next?',
  'options' => ['Enter workforce', 'Community college for credential', 'Community college then transfer','4-year public university', '4-year private university', 'Military','Other']
   ]
);
