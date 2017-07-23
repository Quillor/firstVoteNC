<?php

/**
 * Redirect the user after authentication if there were any errors.
 *
 * @param Wp_User|Wp_Error  $user       The signed in user, or the errors that have occurred during login.
 * @param string            $username   The user name used to log in.
 * @param string            $password   The password used to log in.
 *
 * @return Wp_User|Wp_Error The logged in user, or error information if there were errors.
 */
add_filter( 'authenticate', function( $user, $username, $password ) {
  // Check if the earlier authenticate filter (most likely,
  // the default WordPress authentication) functions have found errors
  if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    if ( is_wp_error( $user ) ) {
      $error_codes = join( ',', $user->get_error_codes() );

      $login_url = home_url( 'teacher-login' );
      $login_url = add_query_arg( 'login', $error_codes, $login_url );

      wp_redirect( $login_url );
      exit;
    }
  }

  return $user;
}, 101, 3 );


/**
 * Returns the URL to which the user should be redirected after the (successful) login.
 *
 * @param string           $redirect_to           The redirect destination URL.
 * @param string           $requested_redirect_to The requested redirect destination URL passed as a parameter.
 * @param WP_User|WP_Error $user                  WP_User object if login was successful, WP_Error object otherwise.
 *
 * @return string Redirect URL
 */
add_filter( 'login_redirect', function( $redirect_to, $requested_redirect_to, $user ) {
  $redirect_url = home_url();

  if ( ! isset( $user->ID ) ) {
    return $redirect_url;
  }

  if ( user_can( $user, 'manage_options' ) ) {
    // Redirect to admin dashboard.
    $redirect_url = admin_url();
  } else {
    // Non-admin users always go to their account page after login
    $blog = get_active_blog_for_user( $user->ID );
    $redirect_url = $blog->siteurl . '?manage';
  }

  return wp_validate_redirect( $redirect_url, home_url() );
}, 10, 3 );
