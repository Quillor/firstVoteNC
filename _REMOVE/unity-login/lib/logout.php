<?php

/**
 * Redirect to custom login page after the user has been logged out.
 */
add_action( 'wp_logout', function() {
  $redirect_url = network_site_url( 'teacher-login?logged_out=true' );
  wp_safe_redirect( $redirect_url );
  exit;
} );
