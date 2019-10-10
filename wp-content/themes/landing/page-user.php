<?php
/**
 * Template Name:: Display Users Page
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */
  
global  $wpdb;
$list = array();


 	
if(isset($_GET['data'])) {
	$user_ids = get_users( array(
		'blog_id' => '',
		'fields'  => 'ID',
	) );

	//add user meta data for roles.
	foreach ( $user_ids as $user_id ) {
		/*if(user_can($user_id, 'edit_pages') && is_super_admin( $user_id )){
			update_user_meta( $user_id, 'wp-custom-role', 'Super Admin');
		}
		else if(user_can($user_id, 'edit_pages')){
			update_user_meta( $user_id, 'wp-custom-role', 'Precinct Director');
		}else{
			update_user_meta( $user_id, 'wp-custom-role', 'Teacher');
		}
		
		$site_id = get_the_author_meta('primary_blog' ,$user_id);
		$sitedetails =  get_blog_details($site_id);
		update_user_meta( $user_id, 'wp-custom-blog-name', $sitedetails->blogname);
		/*
		if($user_id == 738){
			$site_id = get_the_author_meta('primary_blog' ,738);
		    $user_info = get_userdata(738);
		    $all_meta_for_user = get_user_meta(738);
			print_r( $all_meta_for_user['wp_'.$site_id.'_capabilities'][0]);
			if (preg_match('/"([^"]+)"/', $all_meta_for_user['wp_'.$site_id.'_capabilities'][0], $m)) {
				print $m[1];   
			} 
		}*/
	}
}
?>
  
	<div class="text-center extra-padding">
	<?php
		$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$actual_link = explode('?',$actual_link);
	?>
	<a href="?data=refresh" class="btn btn-primary btn-xs" style=" display:none; margin-bottom: 30px; ">Refresh User List</a>
	<a href="<?php echo get_site_url();?>/users_data.csv" class="btn btn-default btn-xs" style="    margin-bottom: 30px; display: inline-block;margin-left: 30px;">Download CSV</a>
	<table class="table table-striped table-bordered table-hover table-responsive">
		<thead class="thead-default">
			<tr class="text-center ">
				<th  class="text-center ">Username</th>
				<th  class="text-center ">Full Name</th>
				<th  class="text-center ">Email</th>
				<th  class="text-center ">Reg. Date</th>
				<th  class="text-center ">WP Role</th>
				<th  class="text-center ">Role</th>
				<th  class="text-center ">Site ID</th>
				<th  class="text-center ">Site Name</th>
				<th  class="text-center ">Site Url</th>
				<th  class="text-center ">Site Address</th>
				<th  class="text-center ">Site City</th>
				<th  class="text-center ">Site Zip</th>
				<th  class="text-center ">Last Login</th>
			</tr>
		</thead>
				<?php
				$wp_user_search = $wpdb->get_results("SELECT ID, display_name FROM $wpdb->users ORDER BY ID");
			
				foreach ( $wp_user_search as $userid ) {
					$user_info = get_userdata($userid->ID);
					$sitedetails =  get_blog_details($user_info->primary_blog);
					//add_user_meta( $user_id, 'wp-custom-blog-name', $sitedetails->blogname);

					//$return  = '';
					//$return .= "\t" . '<li>'.  $userid .' </li>' . "\n";
					
					//wp roles
					$wp_role = '';
					/*
					if(get_user_meta( $userid->ID, 'wp-custom-role', true ) == 'Super Admin'){
						$wp_role = ucfirst(implode(', ', $user_info->roles));
					}else if(get_user_meta( $userid->ID, 'wp-custom-role', true ) == 'Precinct Director') {
						$wp_role =  ucfirst(implode(', ', $user_info->roles));
					}else{
						$wp_role = 'Contributor';
					}
						
					if($wp_role == '' || $wp_role == null){
						$wp_role =  'Editor';
					}*/

					
					echo '<tr>';
					echo '<td>'.stripslashes($user_info->user_login).'</td>';
					echo '<td>'.stripslashes($userid->display_name).'</td>';
					echo '<td>'.stripslashes($user_info->user_email).'</td>';
					echo '<td>'.stripslashes($user_info->user_registered).'</td>';
					
					//echo '<td>'.stripslashes($wp_role).'</td>';
					$site_id = get_the_author_meta('primary_blog' ,$userid->ID);
					$all_meta_for_user = get_user_meta($userid->ID);
					if($userid->ID == 1 || $userid->ID == 2){
						$wp_role = 'Super Admin';
					}
					else if (preg_match('/"([^"]+)"/', $all_meta_for_user['wp_'.$site_id.'_capabilities'][0], $m)){
						$wp_role = $m[1];
					} 
					echo '<td>'.stripslashes(ucfirst($wp_role)).'</td>';
					
					$wp_custom_role = '';
					if($wp_role == 'editor' || $wp_role == 'Super Admin'){
						$wp_custom_role = 'Precinct Director';
					}else{
						$wp_custom_role  =  'Teacher';
					}	
					echo '<td>'.stripslashes($wp_custom_role).'</td>';
					
					echo '<td>'.stripslashes($user_info->primary_blog).'</td>';
					//echo '<td>'.stripslashes(get_user_meta( $userid->ID, 'wp-custom-blog-name', true )).'</td>';
					echo '<td>'.stripslashes( $sitedetails->blogname).'</td>';
					
					$data_id = explode("-",str_replace('/','',$sitedetails->path));
					
					echo '<td>'.stripslashes($sitedetails->path).'</td>';
					echo '<td>'.stripslashes(get_post_meta($data_id[1], '_cmb_address_1', true)).'</td>';
					echo '<td>'.stripslashes(get_post_meta($data_id[1], '_cmb_city', true)).'</td>';
					echo '<td>'.stripslashes(get_post_meta($data_id[1], '_cmb_zip', true)).'</td>';
					
					
					$format = apply_filters( 'wpll_date_format', get_option( 'date_format' ) );
					$last_login_value  = date_i18n( $format, get_user_meta( $userid->ID, 'wp-last-login', true ) );
					if('January 1, 1970' == $last_login_value || $last_login_value == null || $last_login_value == ''){
						$last_login_value = 'Never';
					}
					
					echo '<td>'.stripslashes($last_login_value).'</td>';
					echo '</tr>';
					
					$data = stripslashes($user_info->user_login).'::'.stripslashes($userid->display_name).'::'.stripslashes($user_info->user_email).'::'.
							stripslashes($user_info->user_registered).'::'.stripslashes(ucfirst($wp_role)).'::'.stripslashes($wp_custom_role).'::'.stripslashes($user_info->primary_blog).'::'.
							stripslashes($sitedetails->blogname).'::'.stripslashes($sitedetails->path).'::'.stripslashes(get_post_meta($data_id[1], '_cmb_address_1', true)).'::'.
							stripslashes(get_post_meta($data_id[1], '_cmb_city', true)).'::'.stripslashes(get_post_meta($data_id[1], '_cmb_zip', true)).'::'.$last_login_value;
					array_push($list, $data );
				}
				
				
				?>
	</table>
	<?php 
		
	$headers = ['Username', 'Full Name', 'Email', 'Registered Date', 'WP Role', 'Role', 'Site ID', 'Site Name', 'Site Url', 'Site Address', 'Site City', 'Site Zip', 'Last Login'];
		
	unlink('users_data.csv');
	$file = fopen("users_data.csv","w");

	fputcsv($file, $headers);
	foreach ($list as $line)
	  {
	  fputcsv($file,explode('::',$line));
	 }

	fclose($file); 

?>

</div>

