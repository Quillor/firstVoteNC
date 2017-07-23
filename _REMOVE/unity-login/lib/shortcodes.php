<?php

/**
 * A shortcode for rendering the new user registration form.
 *
 * @param  array   $attributes  Shortcode attributes.
 * @param  string  $content     The text content for shortcode. Not used.
 *
 * @return string  The shortcode output
 */
add_shortcode( 'unity-login-form', function($attributes, $content = null) {
  // Parse shortcode attributes
  $default_attributes = array( 'show_title' => false );
  $attributes = shortcode_atts( $default_attributes, $attributes );

  // Check if the user just requested a new password
  $attributes['lost_password_sent'] = isset( $_REQUEST['checkemail'] ) && $_REQUEST['checkemail'] == 'confirm';

  // Check if user just updated password
  $attributes['password_updated'] = isset( $_REQUEST['password'] ) && $_REQUEST['password'] == 'changed';

  // Check if user just logged out
  $attributes['logged_out'] = isset( $_REQUEST['logged_out'] ) && $_REQUEST['logged_out'] == true;

  // Error messages
  $errors = array();
  if ( isset( $_REQUEST['login'] ) ) {
    $error_codes = explode( ',', $_REQUEST['login'] );

    foreach ( $error_codes as $code ) {
      $errors []= get_error_message( $code );
    }
  }
  $attributes['errors'] = $errors;

  $return = '';

  if ( is_user_logged_in() ) {
    $blog = get_active_blog_for_user( get_current_user_id() );
    wp_redirect($blog->siteurl);
    exit;
  } else {
    if ( $attributes['lost_password_sent'] ) {
      $return .= '<div class="alert alert-info" role="alert">Check your email for a link to reset your password.</div>';
    }

    if ( $attributes['password_updated'] ) {
      $return .= '<div class="alert alert-info" role="alert">Your password has been changed. You can sign in now.</div>';
    }

    if ( $attributes['logged_out'] ) {
      $return .= '<div class="alert alert-info" role="alert">You have signed out. Would you like to sign in again?</div>';
    }

    if ( count( $attributes['errors'] ) > 0 ) {
      foreach ( $attributes['errors'] as $error ) {
        $return .= '<div class="alert alert-danger" role="alert">' . $error . '</div>';
      }
    }

    ob_start();
      // Add wpe-login query arg if we're on production site
      $loginurl = wp_login_url();
      if (function_exists('is_wpe')) {
        $loginurl = add_query_arg(['wpe-login' => 'firstvotenc'], $loginurl);
      }
      ?>
        <form method="post" action="<?php echo $loginurl; ?>">
          <div class="form-group">
            <label for="user_login"><?php _e( 'Email', 'unity-login' ); ?></label>
            <input type="text" class="form-control" name="log" id="user_login">
          </div>
          <div class="form-group">
            <label for="user_pass"><?php _e( 'Password', 'unity-login' ); ?></label>
            <input type="password" class="form-control" name="pwd" id="user_pass">
          </div>
          <div class="form-group">
            <label for="rememberme"><input name="rememberme" type="checkbox" id="rememberme" value="forever" /> Remember Me</label>
          </div>
          <div class="form-group">
            <input type="submit" class="btn btn-default" value="<?php _e( 'Sign In', 'unity-login' ); ?>">
          </div>
        </form>
      <?php
    $return .= ob_get_clean();

    $return .= '<a href="' . wp_lostpassword_url( get_permalink() ) . '">Lost Password?</a>';
    return $return;
  }
} );


/**
 * A shortcode for rendering the form used to initiate the password reset.
 *
 * @param  array   $attributes  Shortcode attributes.
 * @param  string  $content     The text content for shortcode. Not used.
 *
 * @return string  The shortcode output
 */
add_shortcode( 'unity-password-lost-form', function($attributes, $content = null) {
  // Parse shortcode attributes
  $default_attributes = array( 'show_title' => false );
  $attributes = shortcode_atts( $default_attributes, $attributes );

  if ( is_user_logged_in() ) {
    return '<div class="alert alert-info" role="alert">You are already signed in.</div>';
  } else {

    // Retrieve possible errors from request parameters
    $attributes['errors'] = array();
    if ( isset( $_REQUEST['errors'] ) ) {
      $error_codes = explode( ',', $_REQUEST['errors'] );

      foreach ( $error_codes as $error_code ) {
        $attributes['errors'] []= get_error_message( $error_code );
      }
    }

    // Render form
    ob_start();
      if ( count( $attributes['errors'] ) > 0 ) {
        foreach ( $attributes['errors'] as $error ) {
          echo '<div class="alert alert-danger" role="alert">' .  $error . '</div>';
        }
      }

      // Add wpe-login query arg if we're on production site
      $lostpassword_url = wp_lostpassword_url();
      if (function_exists('is_wpe')) {
        $lostpassword_url = add_query_arg(['wpe-login' => 'firstvotenc'], $lostpassword_url);
      }
      ?>
      <p>If you forgot your password, no worries. Just enter your email address and we'll send you a link you can use to pick a new password.</p>
      <form id="lostpasswordform" action="<?php echo $lostpassword_url; ?>" method="post">
        <div class="form-group">
          <label for="user_login">Email</label>
          <input type="text" name="user_login" id="user_login" class="form-control">
        </div>

        <input type="submit" name="submit" class="btn btn-default lostpassword-button" value="Reset Password" />
      </form>
      <?php
    $form = ob_get_clean();
    return $form;
  }
} );


/**
 * A shortcode for rendering the form used to reset a user's password.
 *
 * @param  array   $attributes  Shortcode attributes.
 * @param  string  $content     The text content for shortcode. Not used.
 *
 * @return string  The shortcode output
 */
add_shortcode( 'unity-password-reset-form', function( $attributes, $content = null ) {
    // Parse shortcode attributes
    $default_attributes = array( 'show_title' => false );
    $attributes = shortcode_atts( $default_attributes, $attributes );

    if ( is_user_logged_in() ) {
        return __( 'You are already signed in.', 'unity-login' );
    } else {
        if ( isset( $_REQUEST['login'] ) && isset( $_REQUEST['key'] ) ) {
            $attributes['login'] = $_REQUEST['login'];
            $attributes['key'] = $_REQUEST['key'];

            // Error messages
            $errors = array();
            if ( isset( $_REQUEST['error'] ) ) {
                $error_codes = explode( ',', $_REQUEST['error'] );

                foreach ( $error_codes as $code ) {
                    $errors []= $this->get_error_message( $code );
                }
            }
            $attributes['errors'] = $errors;

            // Render form
            ob_start();
              if ( count( $attributes['errors'] ) > 0 ) {
                foreach ( $attributes['errors'] as $error ) {
                  echo '<div class="alert alert-danger" role="alert">' .  $error . '</div>';
                }
              }

              // Add wpe-login query arg if we're on production site
              $loginurl = wp_login_url();
              $loginurl = add_query_arg(['action' => 'resetpass'], wp_login_url());
              if (function_exists('is_wpe')) {
                $loginurl = add_query_arg(['wpe-login' => 'firstvotenc'], $loginurl);
              }
              ?>
              <form name="resetpassform" id="resetpassform" action="<?php echo $loginurl; ?>" method="post" autocomplete="off">
                <input type="hidden" id="user_login" name="rp_login" value="<?php echo esc_attr( $attributes['login'] ); ?>" autocomplete="off" />
                <input type="hidden" name="rp_key" value="<?php echo esc_attr( $attributes['key'] ); ?>" />

                <div class="form-group">
                  <label for="pass1"><?php _e( 'New password', 'unity-login' ) ?></label>
                  <input type="password" name="pass1" id="pass1" class="form-control" size="20" value="" autocomplete="off" />
                </div>
                <div class="form-group">
                  <label for="pass2"><?php _e( 'Repeat new password', 'unity-login' ) ?></label>
                  <input type="password" name="pass2" id="pass2" class="form-control" size="20" value="" autocomplete="off" />
                </div>

                <div class="form-group"><?php echo wp_get_password_hint(); ?></div>

                <div class="form-group resetpass-submit">
                  <input type="submit" name="submit" id="resetpass-button" class="btn btn-default" value="<?php _e( 'Reset Password', 'unity-login' ); ?>" />
                </p>
              </form>
              <?php
            $form = ob_get_clean();
            return $form;

        } else {
            return __( 'Invalid password reset link.', 'unity-login' );
        }
    }
} );
