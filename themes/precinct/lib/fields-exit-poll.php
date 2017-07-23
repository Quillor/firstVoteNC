<?php

namespace Roots\Sage\CMB;

$prefix = '_cmb_';

$ep_fields = [
  [
    'name' => 'What grade are you in?',
    'label' => 'grade',
    'id' => $prefix . 'grade',
    'type' => 'radio',
    'options' => [
      'Elementary' => 'Kindergarten &ndash; 5th grade',
      'Middle' => '6th &ndash; 8th grade',
      '9' => '9th grade',
      '10' => '10th grade',
      '11' => '11th grade',
      '12' => '12th grade or higher',
    ],
    'attributes' => [
      'data-validation' => 'required'
    ],
    'render_row_cb' => __NAMESPACE__ . '\\accessible_fields_cb'
  ],[
    'name' => 'Gender',
    'label' => 'gender',
    'id' => $prefix . 'gender',
    'type' => 'radio',
    'options' => [
      'Female' => 'Female',
      'Male' => 'Male'
    ],
    'render_row_cb' => __NAMESPACE__ . '\\accessible_fields_cb'
  ],[
    'name' => 'Race/Ethnicity',
    'label' => 'race/ethnicity',
    'id' => $prefix . 'race',
    'type' => 'radio',
    'options' => [
      'Asian/Pacific Islander' => 'Asian/Pacific Islander',
      'Black' => 'Black',
      'Hispanic' => 'Hispanic',
      'White' => 'White',
      'Native American' => 'Native American',
      'Other' => 'Other'
    ],
    'render_row_cb' => __NAMESPACE__ . '\\accessible_fields_cb'
  ],[
    'name' => 'Do the adults in your household vote?',
    'label' => 'adults in household who vote',
    'id' => $prefix . 'adults_vote',
    'type' => 'radio',
    'options' => [
      'No' => 'No',
      'Yes' => 'Yes',
      'Don\'t know' => 'I don\'t know'
    ],
    'render_row_cb' => __NAMESPACE__ . '\\accessible_fields_cb'
  ],[
    'name' => 'Do you plan on registering to vote when you are eligible?',
    'label' => 'plans to register to vote',
    'id' => $prefix . 'register',
    'type' => 'radio',
    'options' => [
      'No' => 'No',
      'Yes' => 'Yes'
    ],
    'render_row_cb' => __NAMESPACE__ . '\\accessible_fields_cb'
  ],[
    'name' => 'If you were to register today, what would be your party affiliation?',
    'label' => 'future party affiliation',
    'id' => $prefix . 'party',
    'type' => 'radio',
    'options' => [
      'Democrat' => 'Democrat',
      'Libertarian' => 'Libertarian',
      'Republican' => 'Republican',
      'Unaffiliated' => 'Unaffiliated',
      'Don\'t know' => 'I don\'t know'
    ],
    'render_row_cb' => __NAMESPACE__ . '\\accessible_fields_cb'
  ],[
    'name' => 'What is your primary source of political news?',
    'label' => 'primary source of political news',
    'id' => $prefix . 'news',
    'type' => 'radio',
    'options' => [
      'Friends and family' => 'Friends and family',
      'Newspapers and magazines' => 'Newspapers and magazines',
      'Social Media' => 'Social Media',
      'Television' => 'Television'
    ],
    'render_row_cb' => __NAMESPACE__ . '\\accessible_fields_cb'
  ],[
    'name' => 'How often do you attend a religious service?',
    'label' => 'frequency of religious services',
    'id' => $prefix . 'religious',
    'type' => 'radio',
    'options' => [
      'More than once per week' => 'More than once per week',
      'Weekly' => 'Weekly',
      'Infrequently' => 'Infrequently',
      'Never' => 'Never'
    ],
    'render_row_cb' => __NAMESPACE__ . '\\accessible_fields_cb'
  ]
];
