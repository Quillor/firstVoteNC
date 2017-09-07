<?php

class Unity_Login {

  /**
   * Initializes the plugin.
   *
   * To keep the initialization fast, only add filter and action
   * hooks in the constructor.
   */
  public function __construct() {
    $includes = [
      '/lib/emails.php',  // Customize emails that are sent
      '/lib/errors.php',  // Error messages
      '/lib/login.php',  // Functions that handle logging in
      '/lib/logout.php',  // Functions that handle logging out
      '/lib/password.php',  // Functions that handle password recovery
      '/lib/shortcodes.php', // Register shortcodes
    ];

    foreach ($includes as $file) {
      $filepath = dirname(__FILE__) . $file;
      if (!file_exists($filepath)) {
        trigger_error(sprintf(__('Error locating %s for inclusion', 'sage'), $filepath), E_USER_ERROR);
      }

      require_once $filepath;
    }
    unset($file, $filepath);
  }

  /**
   * Activate the plugin
   */
  public static function activate() {
    // Information needed for creating the plugin's pages
    $page_definitions = array(
      'teacher-login' => array(
        'title' => __( 'Teacher Sign In', 'unity-login' ),
        'content' => '[unity-login-form]'
      ),
      'lost-password' => array(
        'title' => __( 'Forgot Your Password?', 'unity-login' ),
        'content' => '[unity-password-lost-form]'
      ),
      'reset-password' => array(
        'title' => __( 'Pick a New Password', 'unity-login' ),
        'content' => '[unity-password-reset-form]'
      )
    );

    foreach ( $page_definitions as $slug => $page ) {
      // Check that the page doesn't exist already
      $query = new WP_Query( 'pagename=' . $slug );
      if ( ! $query->have_posts() ) {
        // Add the page using the data from the array above
        wp_insert_post(
          array(
            'post_content'   => $page['content'],
            'post_name'      => $slug,
            'post_title'     => $page['title'],
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'ping_status'    => 'closed',
            'comment_status' => 'closed',
          )
        );
      }
    }
  }

  /**
   * Deactivate the plugin
   */
  public static function deactivate() {
    // Do nothing
  }

}

if (class_exists('Unity_Login')) {
  // Installation and uninstallation hooks
  register_activation_hook(__FILE__, array('Unity_Login', 'activate'));
  register_deactivation_hook(__FILE__, array('Unity_Login', 'deactivate'));

  // instantiate the plugin class
  $unity_login = new Unity_Login();
}
