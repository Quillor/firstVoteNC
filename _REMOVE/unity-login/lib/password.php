<?php

add_action( 'login_form_lostpassword', function() {
  /**
   * Redirects the user to the custom "Forgot your password?" page instead of
   * wp-login.php?action=lostpassword.
   */

  if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {
    if ( is_user_logged_in() ) {
      $this->redirect_logged_in_user();
      exit;
    }

    wp_redirect( home_url( 'lost-password' ) );
    exit;
  }

  /**
   * Initiates password reset.
   */

  if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
    $errors = retrieve_password();
    if ( is_wp_error( $errors ) ) {
      // Errors found
      $redirect_url = home_url( 'lost-password' );
      $redirect_url = add_query_arg( 'errors', join( ',', $errors->get_error_codes() ), $redirect_url );
    } else {
      // Email sent
      $redirect_url = home_url( 'teacher-login' );
      $redirect_url = add_query_arg( 'checkemail', 'confirm', $redirect_url );
    }

    wp_redirect( $redirect_url );
    exit;
  }
} );


/**
 * Returns the message body for the password reset mail.
 * Called through the retrieve_password_message filter.
 *
 * @param string  $message    Default mail message.
 * @param string  $key        The activation key.
 * @param string  $user_login The username for the user.
 * @param WP_User $user_data  WP_User object.
 *
 * @return string   The mail message to send.
 */
add_filter( 'retrieve_password_message', function( $message, $key, $user_login, $user_data ) {
  // Create new message
  $msg  = __( 'Hello!', 'unity-login' ) . "\r\n\r\n";
  $msg .= sprintf( __( 'You asked us to reset your password for your account using the email address %s.', 'unity-login' ), $user_login ) . "\r\n\r\n";
  $msg .= __( "If this was a mistake, or you didn't ask for a password reset, just ignore this email and nothing will happen.", 'unity-login' ) . "\r\n\r\n";
  $msg .= __( 'To reset your password, visit the following address:', 'unity-login' ) . "\r\n\r\n";
  $msg .= site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . "\r\n\r\n";
  $msg .= __( 'Thanks!', 'unity-login' ) . "\r\n";

  return $msg;
}, 10, 4 );


add_action( 'login_form_rp', 'unity_login_reset_password' );
add_action( 'login_form_resetpass', 'unity_login_reset_password' );
function unity_login_reset_password() {

  /**
   * Redirects to the custom password reset page, or the login page
   * if there are errors.
   */
  if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {
    // Verify key / login combo
    $user = check_password_reset_key( $_REQUEST['key'], $_REQUEST['login'] );
    if ( ! $user || is_wp_error( $user ) ) {
      if ( $user && $user->get_error_code() === 'expired_key' ) {
        wp_redirect( home_url( 'teacher-login?login=expiredkey' ) );
      } else {
        wp_redirect( home_url( 'teacher-login?login=invalidkey' ) );
      }
      exit;
    }

    $redirect_url = home_url( 'reset-password' );
    $redirect_url = add_query_arg( 'login', esc_attr( $_REQUEST['login'] ), $redirect_url );
    $redirect_url = add_query_arg( 'key', esc_attr( $_REQUEST['key'] ), $redirect_url );

    wp_redirect( $redirect_url );
    exit;
  }


  /**
   * Resets the user's password if the password reset form was submitted.
   */
  if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
   $rp_key = $_REQUEST['rp_key'];
   $rp_login = $_REQUEST['rp_login'];

   $user = check_password_reset_key( $rp_key, $rp_login );

   if ( ! $user || is_wp_error( $user ) ) {
     if ( $user && $user->get_error_code() === 'expired_key' ) {
       wp_redirect( home_url( 'teacher-login?login=expiredkey' ) );
     } else {
       wp_redirect( home_url( 'teacher-login?login=invalidkey' ) );
     }
     exit;
   }

   if ( isset( $_POST['pass1'] ) ) {
     if ( $_POST['pass1'] != $_POST['pass2'] ) {
       // Passwords don't match
       $redirect_url = home_url( 'reset-password' );

       $redirect_url = add_query_arg( 'key', $rp_key, $redirect_url );
       $redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );
       $redirect_url = add_query_arg( 'error', 'password_reset_mismatch', $redirect_url );

       wp_redirect( $redirect_url );
       exit;
     }

     if ( empty( $_POST['pass1'] ) ) {
       // Password is empty
       $redirect_url = home_url( 'reset-password' );

       $redirect_url = add_query_arg( 'key', $rp_key, $redirect_url );
       $redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );
       $redirect_url = add_query_arg( 'error', 'password_reset_empty', $redirect_url );

       wp_redirect( $redirect_url );
       exit;
     }

     // Parameter checks OK, reset password
     reset_password( $user, $_POST['pass1'] );
     wp_redirect( home_url( 'teacher-login?password=changed' ) );
   } else {
     echo "Invalid request.";
   }

   exit;
  }

}
