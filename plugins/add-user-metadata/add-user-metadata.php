<?php

/* 
Plugin Name: Import User Meta Data from CSV
Plugin URI: http://qstudio.us/plugins/
Description: Bulk add user Metadata from a text list ( csv ) - checking for existing usermeta data to avoid duplicate entries.
Version: 0.3.2
Author: Q Studio
Author URI: http://qstudio.us/
License: GPL2
Text Domain: q-add-user-metadata
Class: Q_Add_User_Metadata
Instance: $q_add_user_metadata
*/

// quick check :) ##
defined( 'ABSPATH' ) OR exit;

if ( ! class_exists( "Q_Add_User_Metadata" ) ) {
    
    if ( is_admin() ) {
    
        // instatiate plugin via WP plugins_loaded - init was too late for CPT ##
        add_action( 'init', array ( 'Q_Add_User_Metadata', 'init' ), 1 );

    }
    
    // define constants ##
    define( 'Q_ADD_USER_METADATA_VERSION', '0.3.2' ); // version ##
    define( 'Q_ADD_USER_METADATA_DEBUG', false ); // debugging ##

    class Q_Add_User_Metadata {

        var $plugin_dir_path;
        var $plugin_URL;
        var $textarea_placeholder;
        var $title_placeholder;
        
        
        /**
        * Creates a new instance.
        *
        * @wp-hook      init
        * @see          __construct()
        * @since        0.1
        * @return       void
        */
        public static function init() 
        {
            new self;
        }
        
        
        /**
	 * Class contructor
	 *
	 * @since   0.1
	 **/
	public function __construct() 
        {
            
            // activation ##
            register_activation_hook( __FILE__, array ( $this, 'register_activation_hook' ) );
            
            // deactivation ##
            register_deactivation_hook( __FILE__, array ( $this, 'register_deactivation_hook' ) );
            
            // uninstall ##
            // TODO ##
            
            if ( is_admin() ) {
                
                // text-domain ##
                add_action ( 'plugins_loaded', array ( $this, 'load_plugin_textdomain' ), 1 );
                
                // plugin URL ##
                $this->plugin_URL = WP_PLUGIN_URL.'/'.dirname(plugin_basename(__FILE__));
                $this->plugin_dir_path = plugin_dir_url(__FILE__);
                $this->textarea_placeholder = '291,John\n224,Peter\n221,Paul';
                $this->title_placeholder = 'metadata_title';
                
                // process import ##
                add_action ( 'admin_init', array ( $this, 'process' ), 1 );
                
                // menu page ##
                add_action( 'admin_menu', array( $this, 'admin_menu' ) ); // build admin menu ##
                
                // a pinch of scripts and styles ##
                add_action( 'admin_footer', array( $this, 'scripts_and_styles' ), 100000 );
                
            }
            
        }
        
        /*
         * configure DB and update init option
         * 
         * @since   0.1
         */
        static public function register_activation_hook() 
        {
            
            $q_add_user_metadata_option = array( 'configured' => true );
            
            // init running, so update configuration flag ##
            add_option( 'q-add-user-metadata', $q_add_user_metadata_option, '', true );
            
        }

        
        /* 
         * function to unset configured flag, run on plugin deactivation 
         * 
         * @since   0.1
         */
        static public function register_deactivation_hook() 
        {
            
            // deconfigure plugin ##
            delete_option('q-add-user-metadata');
            
        }

        
        
         /*
         * Load Plugin Text Domain ##
         * 
         * @since   0.1
         */
        static public function load_plugin_textdomain() 
        {
            
            load_plugin_textdomain( 'q-add-user-metadata', false, basename( dirname( __FILE__ ) ) . '/languages' );
            
        }
        
        
        /*
         * Create API admin menu ##
         * 
         * @since   0.1
         */
        public function admin_menu() 
        {
            
            add_users_page( __( 'Add Metadata', 'q-add-user-metadata' ), __( 'Add Metadata', 'q-add-user-metadata' ), 'list_users', 'add-user-metadata', array( $this, 'admin_page' ) );
            
        }
        
        
        /**
        * Display admin page for importing users 
        * 
        * @since    0.1
        */
        public function admin_page() 
        {
            
            // check the capabilities of the user ##
            // http://codex.wordpress.org/Roles_and_Capabilities#edit_users ##
            if ( !current_user_can( 'list_users' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }
            
            // page title ##
?>
            <div class="wrap">
                <div class="icon32" id="icon-users"><br/></div>
                <h2><?php _e('Add User Metadata', 'q-add-user-metadata'); ?></h2>
<?php
        
                // nothing happening? ##
                if ( isset( $_GET['error'] ) ) {
                    echo '<div class="updated"><p><strong>' . __( 'Opps! Something went wrong...', 'q-add-user-metadata' ) . '</strong></p></div>';
                }

                // debug metadata processing ##
                $q_add_user_metadata_option = get_option( 'q-add-user-metadata', false );
                if ( $q_add_user_metadata_option && isset( $q_add_user_metadata_option['debug'] ) ) {
                    
                    echo "<h4 class='can_hide'>".__('Debug Info:','q-add-user-metadata')."</h4>";
                    
                    foreach ( $q_add_user_metadata_option['debug'] as $row ) {
                        
                        echo "<p class='debug can_hide'>{$row}</p>";
                        
                    }
                    
                }
                
                // debug posted options ##
                if ( $q_add_user_metadata_option && isset( $q_add_user_metadata_option['data'] ) ) {
                    
                    echo "<h4 class='can_hide'>".__('Posted Settings:','q-add-user-metadata')."</h4>";
                    
                    foreach ( $q_add_user_metadata_option['data'] as $key => $value ) {
                        
                        echo "<p class='debug can_hide'>{$key} = <strong>{$value}</strong></p>";
                        
                    }
                    
                }
                
                // unset debug & data ##
                unset ( $q_add_user_metadata_option['debug'] );
                unset ( $q_add_user_metadata_option['data'] );

                // update option ##
                update_option( 'q-add-user-metadata', $q_add_user_metadata_option );
                
?>
                <form method="post" id="q-add-user-metadata">
                    
                    <?php wp_nonce_field( 'q-add-user-metadata', '_wpnonce-q-add-user-metadata' ); ?>
                    <table class="form-table">
                        
                        <tr>
                            <th><label for="key"><?php _e( 'Usermeta Key', 'q-add-user-metadata' ); ?></label></th>
                            <td>
                                <input type="text" name="key" id="key" class="regular-text required" placeholder="<?php echo $this->title_placeholder; ?>">
                                <span class="description"><?php 
                                    printf( 
                                        'The name of the Usermeta Key to add. - <a href="%s" target="_blank">%s</a>.', 
                                        esc_url( "http://codex.wordpress.org/Function_Reference/add_user_meta" ), 
                                        esc_html( __("add_user_meta()", "q_support" ) ) 
                                    ); 
                                ?></span>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="values"><?php _e( 'Comma Seperated Values', 'q-add-user-metadata' ); ?></label></th>
                            <td>
                                <textarea name="values" id="values" rows="10" cols="120" class="required"></textarea><br>
                                <span class="description"><label for="description">
                                <?php 
                                    printf( 
                                        'Paste in the CSV data one row per line, in the format [ user_id, value ] - <a href="#" class="tb">%s</a>.', 
                                        esc_html( __("Example Data", "q_support" ) ) 
                                    ); 
                                ?>
                                </span>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="duplicates">
                                    <?php _e( 'Unique Keys', 'q-add-user-metadata' ); ?>
                                </label>
                            </th>
                            <td>
                                <label for="duplicates">
                                    <input type="checkbox" name="duplicates" id="duplicates" value="skip" checked> <?php _e( 'Nothing will be added if a matching key with value already exists for each user.', 'q-add-user-metadata' ); ?>
                                </label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="debug">
                                    <?php _e( 'Debug Output', 'q-add-user-metadata' ); ?>
                                </label>
                            </th>
                            <td>
                                <label for="debug">
                                    <input type="checkbox" name="debug" id="debug" value="show" checked> <?php _e( 'Fill the screen with geeky debug info after running :)', 'q-add-user-metadata' ); ?>
                                </label>
                            </td>
                        </tr>
                        
                        
                    </table>
                    <p class="submit">
                        <input type="hidden" name="_wp_http_referer" value="<?php echo $_SERVER['REQUEST_URI'] ?>" />
                        <input type="submit" id="q_add_user_metadata_submit" name="q_add_user_metadata_submit" class="button-primary" value="<?php _e( 'Add Metadata', 'q-add-user-metadata' ); ?>" />
                    </p>
                </form>

            </div>
<?php
        
        }
        
        
        
        /* 
         * Process imported data  
         * 
         * @since   0.1
         */
        public function process() {
            
            // check for post data ##
            if ( ! empty( $_POST ) && isset( $_POST['_wpnonce-q-add-user-metadata'] ) ) {
                
                // grab value from plugin option ##
                $q_add_user_metadata_option = get_option( 'q-add-user-metadata', array() );
                
                // validate nonce values ##
                if ( check_admin_referer( 'q-add-user-metadata', '_wpnonce-q-add-user-metadata' ) ) {
                    
                    // tracking variable - start out positive ##
                    $good_to_go = true;
                    
                    // let's build an empty array ##
                    $process = array();
                    
                    // was the "key" passed ##
                    if ( isset( $_POST['key'] ) && $_POST['key'] != '' ) {
                        
                        $process['key'] = $this->sanitize( $_POST['key'], 'key' );
                        
                    } else {
                        
                        // stop ## 
                        $good_to_go = false;
                        
                        // update notification
                        $q_add_user_metadata_option['notification'] = __('Metadata Key Empty!','q-add-user-metadata');
                        
                    }
                    
                    // were the "values" passed ##
                    if ( isset( $_POST['values'] ) && $_POST['values'] != '' ) {
                        
                        // break values into array ##
                        $values = $this->csvstring_to_array( $_POST['values'] );
                        
                        // add to array ##
                        $process['values'] = $values;
                        
                        if ( !is_array($process['values']) ) {
                            
                            // stop ## 
                            $good_to_go = false;
                            
                        }
                        
                    } else {
                        
                        // stop ## 
                        $good_to_go = false;
                        
                        // update notification
                        $q_add_user_metadata_option['notification'] = __('Metadata Values Empty!','q-add-user-metadata');
                        
                    }
                    
                    // was the "duplicates" passed ##
                    if ( isset( $_POST['duplicates'] ) && $_POST['duplicates'] == 'skip' ) {

                        $process['duplicates'] = 'skip';
                    
                    } else {
                        
                        $process['duplicates'] = 'allow';
                        
                    }
                    
                    // was the "debug" passed ##
                    if ( isset( $_POST['debug'] ) && $_POST['debug'] == 'show' ) {

                        $process['debug'] = 'show';
                    
                    } else {
                        
                        $process['debug'] = 'hide';
                        
                    }
                    
                    // let's process!! ##
                    if ( $good_to_go === true ) {
                        
                        // so - let's do it! ##
                        $debug = array();
                        
                        foreach( $process['values'] as $row ) {
                            
                            // get user ID ##
                            $user_id = $row[0];
                            
                            // get the user from the ID ##
                            $user = get_user_by( 'id', $user_id );
                            
                            if ( $user === false ) {
                                
                                // save for debug ##
                                $debug[] = sprintf( 
                                        __( "%s This user does not exist, so I'm skipping this one.", "q-add-user-metadata" ) 
                                        , "( <strong>$user_id </strong> ) -" 
                                    ); 
                                
                                // and skip on ##
                                continue;

                            }
                            
                            // get metadata value ##
                            $value = $row[1];
                            
                            // default to ok ##
                            $check = false;
                            
                            // check for duplicate values ##
                            if ( $process['duplicates'] == 'skip' ) {
                                
                                // check if key exists ##
                                $exists = metadata_exists( 'user', $user_id, $process['key'] );
                                
                                // not found ##
                                if ( $exists === true ) {
                                    
                                    // check for value ##
                                    $exists_value = get_user_meta( $user_id, $process['key'], true );

                                    if ( empty( $exists_value ) || $exists_value == '' ) {
                                        
                                        // save for debug ##
                                        #$debug[] = "( <strong>{$user_id}</strong> ) {$user->user_login} - key <strong>{$process['key']}</strong> existed, but was empty - so I updated the value to <strong>{$value}</strong>";
                                        
                                        $debug[] = sprintf( 
                                            __( "%s Key %s existed, but was empty - so I updated the value to %s", "q-add-user-metadata" )
                                            ,( "( <strong>$user_id </strong> ) {$user->user_login} -") 
                                            ,( "<strong>{$process['key']}</strong>") 
                                            ,( "<strong>{$value}</strong>") 
                                        ); 

                                        // update the existin user meta ##
                                        update_user_meta( $user_id, $process['key'], $value );
                                        
                                        // and skip on ##
                                        continue;
                                        
                                    } else {
                                    
                                        // debug it ##
                                        #$debug[] = "( <strong>{$user_id}</strong> ) {$user->user_login} - key <strong>{$process['key']}</strong> already has the value <strong>{$exists_value}</strong> - skipping";
                                        
                                        $debug[] = sprintf( 
                                            __( "%s Key %s already has the value %s - skipping", "q-add-user-metadata" ) 
                                            ,( "( <strong>$user_id </strong> ) {$user->user_login} -") 
                                            ,( "<strong>{$process['key']}</strong>") 
                                            ,( "<strong>{$exists_value}</strong>") 
                                        ); 
                                        
                                        // and skip on ##
                                        continue;
                                        
                                    }
                                    
                                }
                                
                            }
                            
                            // save for debug ##
                            #$debug[] = "( <strong>{$user_id}</strong> ) {$user->user_login} - added the key <strong>{$process['key']}</strong> with the value <strong>{$value}</strong>";
                            
                            $debug[] = sprintf( 
                                __( "%s Added the key %s with the value %s", "q-add-user-metadata" )
                                ,( "( <strong>$user_id </strong> ) {$user->user_login} -") 
                                ,( "<strong>{$process['key']}</strong>") 
                                ,( "<strong>{$value}</strong>") 
                            ); 
                            
                            // finally - add the user meta !! ##
                            add_user_meta( $user_id, $process['key'], $value );
                            
                        }
                        
                        // quick test ##
                        if ( $process['debug'] === 'show' ) {
                            
                            // save debug lines ##
                            $q_add_user_metadata_option['debug'] = $debug;
                            
                            // clean up the debug values ##
                            unset ( $process['values'] );
                            
                            // save processed data ##
                            $q_add_user_metadata_option['data'] = $process;
                            
                        }
                        
                        // update notification
                        #$q_add_user_metadata_option['notification'] = "Processed ".count($debug)." Metadata Entries.";
                        
                        $q_add_user_metadata_option['notification'] = sprintf( 
                                                                        __( "Processed %d Metadata Entries.", "q-add-user-metadata" )
                                                                        ,count($debug) 
                                                                    ); 
                        
                    }
                    
                // nonce failed ##
                } else {
                    
                    // wrong ##
                    $q_add_user_metadata_option['notification'] =  __('Add Metadata Failed!','q-add-user-metadata');
                    
                }
                
                // update option ##
                update_option( 'q-add-user-metadata', $q_add_user_metadata_option );
                
            }
            
            // trigger notice ##
            add_action( 'admin_notices', array ( $this, 'admin_notice' ), 1 );
            
        }
        
        
        /* 
         * Convert CSV strong to array 
         * 
         * @since   0.1
         */
        public function csvstring_to_array( $string ) 
        {
    
            $lines = explode( "\n", $string );
            $array = array();
            foreach ($lines as $line) {
                $array[] = str_getcsv( $this->sanitize( $line ) );
            }
            
            return $array;
            
        }
        
        
        /*
         * Add Admin Notice ##
         * 
         * @since   0.1
         */
        public function admin_notice() 
        {
            
            $q_add_user_metadata_option = get_option( 'q-add-user-metadata', false );
            
            if ( $q_add_user_metadata_option && isset( $q_add_user_metadata_option['notification'] ) ) {
            
?>
            <div class="updated can_hide">
                <p><?php echo $q_add_user_metadata_option['notification'] ?></p>
                <span class="does_close" title="<?php _e("Hide", "q-add-user-metadata"); ?>"></span>
            </div>
<?php
                
                // unset notification ##
                unset ( $q_add_user_metadata_option['notification'] );

                // update option ##
                update_option( 'q-add-user-metadata', $q_add_user_metadata_option );

            }

        }
        
        
        /* 
         * Add required jquery 
         * 
         * @since   0.1
         */
        public function scripts_and_styles() 
        {
            
            // load the scripts on only the plugin admin page 
            if (isset( $_GET['page'] ) && ( $_GET['page'] == 'add-user-metadata' ) ) {
            
?>
        <script>
            
            jQuery( document ).ready(function() {
                
                // validation ##
                jQuery("body").on('submit', 'form#q-add-user-metadata', function(e) {
                    
                    jQuery("form#q-add-user-metadata input[type=text], form#q-add-user-metadata textarea").each(function(){
                       
                        if( jQuery(this).hasClass("required") && !jQuery(this).val() ) {
                           
                            // stop the form ##
                            e.preventDefault();
                            
                            // highlight error ##
                            jQuery(this).css({"background-color" : "#ffffe0", "border-color" : "#e6db55" });
                            
                            // some feedback ##
                            //console.log("errors!");
                           
                        }
                       
                    });
                  
                });
                
                // remove highlight on focus
                jQuery("body").on('focus', 'form#q-add-user-metadata input[type=text], form#q-add-user-metadata textarea', function(){
                   
                    // highlight error ##
                    jQuery(this).css({"background-color" : "#fff", "border-color" : "#dfdfdf"});
                    
                    // also hide notices above, as user wants to move on !! ##
                    jQuery(".can_hide").hide("fast");
                   
                });
                
                // thick box for example values ##
                jQuery("body").on('click', '.tb', function(e) {
                    
                    e.preventDefault();
                    
                    tb_show( '<?php _e("Example Data", "q_support" ); ?>', '<?php echo $this->plugin_dir_path."example.txt"; ?>?type=text&TB_iframe=1');
                    
                });
                
                // hide notices and debug info ##
                jQuery("body").on('click', '.does_close', function(e) {
                    
                    e.preventDefault();
                    
                    jQuery(".can_hide").hide("fast");
                    
                });
               
            });
            
        </script>
        <style>
            .debug {
                border-bottom: 1px solid #f2f2f2; padding: 0px 10px 7px 0px;
            }
            .wrap div.updated.can_hide {
                position: relative;
            }
            .does_close {
                background: url("<?php echo $this->plugin_dir_path; ?>images/close.png") 0px 0px no-repeat transparent;
                position: absolute;
                top: 8px;
                right: 8px;
                width: 16px;
                height: 16px;
                cursor: pointer;
            }
            
        </style>
<?php

            }
        
        }
        
        
        /**
         * Sanitize user input data ##
         * 
         * @since   0.1
         * @return  string
         * @src     http://codex.wordpress.org/Validating_Sanitizing_and_Escaping_User_Data
         * @src     http://wp.tutsplus.com/tutorials/creative-coding/data-sanitization-and-validation-with-wordpress/
         */
        public function sanitize( $value = null, $type = 'text' )
        {
            
            // check submitted data ##
            if ( !$value ) {
                
                return false;
                
            }
            
            switch ($type) {
                
                case( 'email' ):
                
                    return sanitize_email( $value );
                    break;
                
                case( 'user' ):
                
                    return sanitize_user( $value );
                    break;
                
                case( 'key' ):
                
                    return sanitize_key( $value );
                    break;
                    
                case( 'text' ):
                default;
                     
                    // text validation
                    return sanitize_text_field( $value );
                    break;
                    
            }
            
        }
        
        
    }
    
}