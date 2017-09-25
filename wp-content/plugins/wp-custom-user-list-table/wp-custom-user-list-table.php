<?php
/** wp-custom-user-list-table.php
 *
 * Plugin Name: WP Custom User List Table
 * Plugin URI:  https://github.com/charls637/WP/tree/master/Plugins/wp-custom-user-list-table
 * Description: Displays custom column in user lists.
 * Version:     1.4.0
 * Author:      Charley Birondo
 * Author URI:  https://github.com/charls637
 * Text Domain: wp-custom-user-list-table
 * Domain Path: /lang
 * License:     GPLv2
 */


if ( ! class_exists( 'Obenland_Wp_Plugins_v301' ) ) {
	require_once( 'obenland-wp-plugins.php' );
}


class Obenland_Wp_Custom_User_List_Table extends Obenland_Wp_Plugins_v301 {


	///////////////////////////////////////////////////////////////////////////
	// METHODS, PUBLIC
	///////////////////////////////////////////////////////////////////////////

	/**
	 * Constructor.
	 *
	 * @author modified by Charley Birondo
	 * @since  1.0 - 23.01.2012
	 * @access public
	 *
	 * @return Obenland_Wp_Custom_User_List_Table
	 */
	public function __construct() {
		parent::__construct( array(
			'textdomain'     => 'wp-custom-user-list-table',
			'plugin_path'    => __FILE__,
			'donate_link_id' => 'K32M878XHREQC',
		) );

		load_plugin_textdomain( 'wp-custom-user-list-table', false, 'wp-custom-user-list-table/lang' );

		$this->hook( 'wp_login' );

		/**
		 * Programmers:
		 * To limit this information to certain user roles, add a filter to
		 * 'wpll_current_user_can' and check for user permissions, returning
		 * true or false!
		 *
		 * Example:
		 *
		 * function prefix_wpll_visibility( $bool ) {
		 *     return current_user_can( 'manage_options' ); // Only for Admins
		 * }
		 * add_filter( 'wpll_current_user_can', 'prefix_wpll_visibility' );
		 *
		 */
		if ( is_admin() && apply_filters( 'wpll_current_user_can', true ) ) {

			$this->hook( 'manage_site-users-network_columns',     'add_column', 1 );
			$this->hook( 'manage_users_columns',                  'add_column', 1 );
			$this->hook( 'wpmu_users_columns',                    'add_column', 1 );
			$this->hook( 'admin_print_styles-users.php',          'column_style'  );
			$this->hook( 'admin_print_styles-site-users.php',     'column_style'  );
			$this->hook( 'manage_users_custom_column'                             );
			$this->hook( 'manage_users_sortable_columns',         'add_sortable'  );
			$this->hook( 'manage_users-network_sortable_columns', 'add_sortable'  );
			$this->hook( 'pre_get_users'                                          );
		}
	}

	public function wp_login( $user_login ) {
		$user = get_user_by( 'login', $user_login );
	}

	/**
	 * Adds the last login column to the network admin user list.
	 *
	 * @author modified by Charley Birondo
	 * @since  1.0 - 23.01.2012
	 * @access public
	 *
	 * @param  array $cols The default columns.
	 *
	 * @return array
	 */
	public function add_column( $cols ) {
		$cols['wp_capabilities'] = __( 'Role', 'wp-custom-user-list-table' );
		$cols[ 'blogname' ] = __( 'Site Name', 'wp-custom-user-list-table' );
		$cols[ 'primary_blog' ] = __( 'Site ID', 'wp-custom-user-list-table' );
		return $cols;
	}


	/**
	 * Adds the last login column to the network admin user list.
	 *
	 * @author modified by Charley Birondo
	 * @since  1.0 - 23.01.2012
	 * @access public
	 *
	 * @param  string $value       Value of the custom column.
	 * @param  string $column_name The name of the column.
	 * @param  int    $user_id     The user's id.
	 *
	 * @return string
	 */
	public function manage_users_custom_column( $value, $column_name, $user_id ) {
		if ( 'wp_capabilities' == $column_name ){
			
			if(user_can($user_id, 'edit_pages') && is_super_admin( $user_id )){
				$value = 'Super Admin';
			}
			else if(user_can($user_id, 'edit_pages')){
				$value = 'Precinct Director';
			}else{
				$value =  'Teacher';
			}
			
			
		}
		
		if ( 'blogname' == $column_name ){
			$site_id = get_the_author_meta('primary_blog' ,$user_id);
			$sitedetails =  get_blog_details($site_id);
			$value =  $sitedetails->blogname;
		}
	
		if ( 'primary_blog' == $column_name ){
			$value = the_author_meta('primary_blog' ,$user_id, true);
		}
		
		return $value;
	}


	/**
	 * Register the column as sortable.
	 *
	 * @author modified by Charley Birondo
	 * @since  1.2.0 - 11.12.2012
	 * @access public
	 *
	 * @param  array $columns
	 *
	 * @return array
	 */
	public function add_sortable( $columns ) {
		$columns[ 'wp_capabilities' ] = 'wp-custom-role';
		$columns[ 'blogname' ] = 'wp-custom-blog-name';
		$columns[ 'primary_blog' ] = 'primary_blog';

		return $columns;
	}


	/**
	 * Handle ordering by last login.
	 *
	 * @since  1.2.0 - 11.12.2012
	 * @access public
	 *
	 * @param  WP_User_Query $user_query Request arguments.
	 *
	 * @return WP_User_Query
	 */
	public function pre_get_users( $user_query ) {
		if ( isset( $user_query->query_vars['orderby'] ) && 'wp-custom-role' == $user_query->query_vars['orderby'] ) {
			$user_query->query_vars = array_merge( $user_query->query_vars, array(
				'meta_key' => 'wp-custom-role',
				'orderby'  => 'meta_value',
			) );
		}
		if ( isset( $user_query->query_vars['orderby'] ) && 'wp-custom-blog-name' == $user_query->query_vars['orderby'] ) {
			$user_query->query_vars = array_merge( $user_query->query_vars, array(
				'meta_key' => 'wp-custom-blog-name',
				'orderby'  => 'meta_value',
			) );
		}
		if ( isset( $user_query->query_vars['orderby'] ) && 'primary_blog' == $user_query->query_vars['orderby'] ) {
			$user_query->query_vars = array_merge( $user_query->query_vars, array(
				'meta_key' => 'primary_blog',
				'orderby'  => 'meta_value_num',
			) );
		}

		return $user_query;
	}


	/**
	 * Defines the width of the column
	 *
	 * @author modified by Charley Birondo
	 * @since  1.0 - 23.01.2012
	 * @access public
	 *
	 * @return void
	 */
	public function column_style() {
		echo '<style>#registered{width: 7%}</style>';
		echo '<style>#wp-last-login{width: 7%}</style>';
		echo '<style>.column-wp_capabilities{width: 8%}</style>';
		echo '<style>.column-blogname{width: 13%}</style>';
		echo '<style>.column-primary_blog{width: 5%}</style>';
	}

} // End of class Obenland_Wp_Custom_User_List_Table.


new Obenland_Wp_Custom_User_List_Table;

function wpcult_activate() {
	$user_ids = get_users( array(
		'blog_id' => '',
		'fields'  => 'ID',
	) );
	
	//add user meta data for roles.
	foreach ( $user_ids as $user_id ) {
		if(user_can($user_id, 'edit_pages') && is_super_admin( $user_id )){
			add_user_meta( $user_id, 'wp-custom-role', 'Super Admin');
		}
		else if(user_can($user_id, 'edit_pages')){
			add_user_meta( $user_id, 'wp-custom-role', 'Precinct Director');
		}else{
			add_user_meta( $user_id, 'wp-custom-role', 'Teacher');
		}
		
		$site_id = get_the_author_meta('primary_blog' ,$user_id);
		$sitedetails =  get_blog_details($site_id);
		add_user_meta( $user_id, 'wp-custom-blog-name', $sitedetails->blogname);
		
	}
}

register_activation_hook( __FILE__, 'wpcult_activate' );

/* End of file wp-last-login.php */
/* Location: ./wp-content/plugins/wp-last-login/wp-last-login.php */
