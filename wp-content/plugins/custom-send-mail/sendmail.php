<?php
/**
 * Plugin Name: Custom send mail
 * Description: Overwrites the pluggable 'wp_new_user_notification()' plugin to allow the sending of a custom email
 * Author: Charley Birondo
 * Version: 1.0
 */

if ( !function_exists('wp_new_user_notification') ) :
/**
 * Pluggable - Email login credentials to a newly-registered user
 *
 * A new user registration notification is also sent to admin email.
 *
 * @since 2.0.0
 *
 * @param int    $user_id        User ID.
 * @param string $plaintext_pass Optional. The user's plaintext password. Default empty.
 */
function wp_new_user_notification($user_id, $plaintext_pass = '', $school_id, $school_name, $home_url){
	global $wpdb, $wp_hasher;

    $user = get_userdata($user_id);
    $headers ='Content-type: text/html;charset=utf-8' . "\r\n";

    // The blogname option is escaped with esc_html on the way into the database in sanitize_option
    // we want to reverse this for the plain text arena of emails.
    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

    $message  = sprintf(__('New user registration on your site %s:'), $blogname) . "\r\n\r\n";
    $message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
    $message .= sprintf(__('E-mail: %s'), $user->user_email) . "\r\n";

    @wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), $blogname), $message);

    if ( empty($plaintext_pass) )
        return;

	// Generate something random for a password reset key.
    $key = wp_generate_password( 20, false );
	
	/** This action is documented in wp-login.php */
    do_action( 'retrieve_password_key', $user->user_login, $key );
	
	// Now insert the key, hashed, into the DB.
    if ( empty( $wp_hasher ) ) {
        require_once ABSPATH . WPINC . '/class-phpass.php';
        $wp_hasher = new PasswordHash( 8, true );
    }
    $hashed = time() . ':' . $wp_hasher->HashPassword( $key );
    $wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );
 
    $switched_locale = switch_to_locale( get_user_locale( $user ) );
	
	//$reset_link = $home_url."wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ;
	$reset_link = site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login') ;
	
	$path = str_replace('\\', '/', plugin_dir_path( __FILE__ ));
	$schoolName = $school_name;
	$schoolURL =  $home_url.'/nc-'.$school_id;
	
	$message = '';
	
	include $path.'file.php';
    //$message .=file_get_contents($path.'dev.php');
	add_filter('wp_mail_content_type', function( $content_type ) {
				return 'text/html';
	});
	
    $message .=  '<br /><br />';
    //$message .= sprintf(__('Username: %s'), $user->user_login) . '<br />';
    //$message .= sprintf(__('Password: %s'), $plaintext_pass) . '<br />';
    //$message .= sprintf(__('School Name: %s'), $school_name) . '<br />';
    //$message .= sprintf(__('School URL: %s'), $schoolURL.'/') . '<br />';
    //$message .= sprintf(__('Login: %s'), $home_url) . '/teacher-login/ <br />';
	
    wp_mail($user->user_email, sprintf(__('[%s] Account Information'), $blogname), $message, $headers);

}

endif;