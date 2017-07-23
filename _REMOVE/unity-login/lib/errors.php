<?php

/**
 * Finds and returns a matching error message for the given error code.
 *
 * @param string $error_code    The error code to look up.
 *
 * @return string               An error message.
 */
function get_error_message( $error_code ) {
  switch ( $error_code ) {
    case 'empty_username':
      return __( 'Please enter your email address.', 'unity-login' );

    case 'empty_password':
      return __( 'You need to enter a password to login.', 'unity-login' );

    case 'invalid_username':
      return __(
        "We don't have any users with that email address. Maybe you used a different one when signing up?",
        'unity-login'
      );

    case 'incorrect_password':
      $err = __(
        "The password you entered wasn't quite right. <a href='%s' class='alert-link'>Did you forget your password</a>?",
        'unity-login'
      );
      return sprintf( $err, wp_lostpassword_url() );

    case 'invalid_email':
    case 'invalidcombo':
      return __( 'There are no users registered with this email address.', 'unity-login' );

    case 'expiredkey':
    case 'invalidkey':
      return __( 'The password reset link you used is not valid anymore.', 'personalize-login' );

    case 'password_reset_mismatch':
      return __( "The two passwords you entered don't match.", 'personalize-login' );

    case 'password_reset_empty':
      return __( "Sorry, we don't accept empty passwords.", 'personalize-login' );

    default:
      break;
  }

  return __( 'An unknown error occurred. Please try again later.', 'unity-login' );
}
