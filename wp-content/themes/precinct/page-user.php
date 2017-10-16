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
 ?>
<div class="text-center extra-padding">
<a href="<?php echo get_site_url();?>/users_data.csv">Download CSV</a>
<table>
	<tr class="text-center ">
		<th  class="text-center ">Username</th>
		<th  class="text-center ">Full Name</th>
		<th  class="text-center ">Email</th>
		<th  class="text-center ">Registered Date</th>
		<th  class="text-center ">Site Url</th>
		<th  class="text-center ">Role</th>
		<th  class="text-center ">Site Name</th>
		<th  class="text-center ">Site ID</th>
		<th  class="text-center ">Last Login</th>
	</tr>
			<?php
			$wp_user_search = $wpdb->get_results("SELECT ID, display_name FROM $wpdb->users ORDER BY ID");
		
			foreach ( $wp_user_search as $userid ) {
				$user_info = get_userdata($userid->ID);
				$sitedetails =  get_blog_details($user_info->primary_blog);
				//add_user_meta( $user_id, 'wp-custom-blog-name', $sitedetails->blogname);

				//$return  = '';
				//$return .= "\t" . '<li>'.  $userid .' </li>' . "\n";

				//echo $return;
				//print_r($userid);
				echo '<tr>';
				echo '<td>'.stripslashes($user_info->user_login).'</td>';
				echo '<td>'.stripslashes($userid->display_name).'</td>';
				echo '<td>'.stripslashes($user_info->user_email).'</td>';
				echo '<td>'.stripslashes($user_info->user_registered).'</td>';
				echo '<td>'.stripslashes($sitedetails->path).'</td>';
				echo '<td>'.stripslashes(get_user_meta( $userid->ID, 'wp-custom-role', true )).'</td>';
				echo '<td>'.stripslashes(get_user_meta( $userid->ID, 'wp-custom-blog-name', true )).'</td>';
				echo '<td>'.stripslashes($user_info->primary_blog).'</td>';
				
				$format = apply_filters( 'wpll_date_format', get_option( 'date_format' ) );
				$last_login_value  = date_i18n( $format, get_user_meta( $userid->ID, 'wp-last-login', true ) );
				if('January 1, 1970' == $last_login_value || $last_login_value == null || $last_login_value == ''){
					$last_login_value = 'Never';
				}
				
				echo '<td>'.stripslashes($last_login_value).'</td>';
				echo '</tr>';
				
				$data = stripslashes($user_info->user_login).'::'.stripslashes($userid->display_name).'::'.stripslashes($user_info->user_email).'::'.
						stripslashes($user_info->user_registered).'::'.stripslashes($sitedetails->path).'::'.stripslashes(get_user_meta( $userid->ID, 'wp-custom-role', true )).'::'.
						stripslashes(get_user_meta( $userid->ID, 'wp-custom-blog-name', true )).'::'.stripslashes($user_info->primary_blog).'::'.$last_login_value;
				array_push($list, $data );
			}
			
			?>
</table>
<?php 
	
$headers = ['Username', 'Full Name', 'Email', 'Registered Date', 'Site Url', 'Role', 'Site Name', 'Site ID', 'Last Login'];
	
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

