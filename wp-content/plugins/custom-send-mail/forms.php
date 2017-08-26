<?php

namespace Roots\Sage\Forms;

/*
 * Preprocess function for live sign up form
 *
 */
add_filter('caldera_preprocess_live', function($data){
  global $domain;

  // Get Precinct ID
  $school = get_page_by_path(sanitize_title($data['school_name']), OBJECT, 'precinct');

  // Create new precinct site based on school code
  $school_id = wpmu_create_blog($domain, '/nc-' . $school->ID . '/', $school->post_title, 1, ['current_theme' => 'Precinct', 'template' => 'precinct', 'stylesheet' => 'precinct', 'blogdescription' => 'First Vote NC Precinct']);

  if (is_wp_error($school_id)) {
    // If this school is already registered, set role to contributor.
    $role = 'contributor';
    $school_id = get_id_from_blogname('/nc-' . $school->ID);
  } else {
    $role = 'editor';
  }

  // Generate the password so that the subscriber will have to check email...
  $password = wp_generate_password( 12, false );

  $userdata = array(
    'user_login'  =>  $data['email_address'],
    'user_email'  =>  $data['email_address'],
    'user_pass'   =>  $password,
    'first_name'  =>  $data['first_name'],
    'last_name'   =>  $data['last_name']
  );
 
  // Create new account for user
  $user_id = wp_insert_user( $userdata );

  if ( ! is_wp_error( $user_id ) ) {
    // Add custom user meta
    add_user_meta( $user_id, 'classes', $data['what_do_you_teach'], true );

    // Move user to correct precinct
    remove_user_from_blog($user_id, get_current_site()->blog_id); // remove user from main blog.
    remove_user_from_blog(1, $school_id); // remove wpengine from new blog.
    add_user_to_blog( $school_id, $user_id, $role );
    update_user_meta( $user_id, 'primary_blog', $school_id );
 
    // Send user email
    wp_new_user_notification( $user_id, $password, $school->ID, $school->post_title, get_home_url());

  } else {
    // If user already registered, return error message
    return array(
      'type' => 'error',
      'note'	=> $user_id->get_error_message()
    );
  }
});
