<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
function revglue_pfeeds_subscription_validate() 
{
	global $wpdb;
	// pre($_POST);
	// die;
	$project_table = $wpdb->prefix.'rg_projects';
	$sanitized_sub_id	= sanitize_text_field( $_POST['sub_id'] );
	$sanitized_email	= sanitize_email( $_POST['sub_email'] );
	$password  			= $_POST['sub_pass'];
	$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( RGPFEEDS_API_URL . "api/validate_subscription_key/$sanitized_email/$password/$sanitized_sub_id", array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 
	$result = $resp_from_server['response']['result'];
	$string = '';
	if( $resp_from_server['response']['success'] == true )
	{
		$sql = "SELECT *FROM $project_table WHERE project LIKE '".$result['project']."' and status = 'active'";
	    $execute_query = $wpdb->get_results( $sql );
		$rows = $wpdb->num_rows;
		if( empty ( $rows ) )
		{
			$wpdb->insert( 
				$project_table, 
				array( 
					'subcription_id' 		=> $sanitized_sub_id, 
					'user_name' 			=> $result['user_name'], 
					'email' 				=> $result['email'], 
					'project' 				=> $result['project']=="QuickShop"?str_replace("QuickShop","Product Feeds UK",$result['project']):$result['project'], 
					'expiry_date' 			=> $result['expiry_date'], 
					'partner_iframe_id' 	=> $result['iframe_id'], 
					'password' 				=> $password, 
					'status' 				=> $result['status']
				) 
			);
			$projectType =	rg_project_type();
			if( $projectType=="Free" ){
				$string .= "<div class='panel-white mgBot'>";
				$string .= "<p><b>Your RevEmbed subscription Free Product Feed is ".$result['status'].". </b><img  class='tick-icon' src=".RGPFEEDS_PLUGIN_URL. '/admin/images/tick.png'." />  </p>";
				$string .= "<p><b>Name = </b>".$result['user_name']."</p>";
				$string .= "<p><b>Project = </b>". str_replace("QuickShop","Product Feeds UK",$result['project'])."</p>";
				$string .= "<p><b>Email = </b>".$result['email']."</p>";
				$string .= "</div>";		
			}else{
			$string .= "<div class='panel-white mgBot'>";
				$string .= "<p><b>Your product feeds data subscription is ".$result['status'].". </b><img  class='tick-icon' src=".RGPFEEDS_PLUGIN_URL. '/admin/images/tick.png'." />  </p>";
				$string .= "<p><b>Name = </b>".$result['user_name']."</p>";
				$string .= "<p><b>Project = </b>".$result['project']."</p>";
				$string .= "<p><b>Email = </b>".$result['email']."</p>";
				$string .= "<p><b>Expiry Date = </b>".date("d-M-Y", strtotime( $result['expiry_date'] ))."</p>";
			$string .= "</div>";
			}
		} else 
		{
			$string .= "<div style='color: green;'>You already have subscription of this project, thankyou! </div>";	
		}
	} else 
	{
		$string .= "<p>&raquo; Your subscription unique ID <b class='grmsg'> ". $sanitized_sub_id ." </b> is Invalid.</p>";
	}
	echo $string;
	wp_die();
}
add_action( 'wp_ajax_revglue_pfeeds_subscription_validate', 'revglue_pfeeds_subscription_validate' );
function revglue_pfeeds_data_import()
{
	global $wpdb;
	$project_table = $wpdb->prefix.'rg_projects';
	$categories_table = $wpdb->prefix.'rg_categories';
	$stores_table = $wpdb->prefix.'rg_stores';
	$string = '';
	$date = date('Y-m-d H:i:s');
	$import_type = sanitize_text_field( $_POST['import_type'] );
	$sql = "SELECT *FROM $project_table where project like 'Product Feeds UK'";
	$project_detail = $wpdb->get_results($sql);
	$rows = $wpdb->num_rows;
	if( !empty ( $rows ) )
	{
		$subscriptionid = $project_detail[0]->subcription_id;
		$useremail = $project_detail[0]->email;
		$userpassword = $project_detail[0]->password;
		$projectid = $project_detail[0]->partner_iframe_id;
		if( $import_type == 'rg_stores_import' )
		{
			rg_update_subscription_expiry_date($subscriptionid, $userpassword, $useremail, $projectid);
			$projectType =	rg_project_type();
			$resp_from_server='';
			if($projectType=="Free" ){
				$projectid = $project_detail[0]->partner_iframe_id;
				$apiurl = RGPFEEDS_API_URL . "partner/product_stores/$projectid/json/".$project_detail[0]->subcription_id;
				$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( $apiurl, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 
			}else{
				$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( RGPFEEDS_API_URL . "api/deal_stores/json/".$project_detail[0]->subcription_id, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 
			}
			$result = $resp_from_server['response']['stores']; 
	  		if($resp_from_server['response']['success'] == 1 )
			{
				foreach($result as $row)
				{
					$sqlinstore = "Select rg_store_id FROM $stores_table Where rg_store_id = '".$row['rg_store_id']."'";
					$rg_store_exists = $wpdb->get_var( $sqlinstore );
					if( empty( $rg_store_exists ) )
					{
						$wpdb->insert( 
							$stores_table, 
							array( 
								'rg_store_id' 				=> $row['rg_store_id'], 
								'mid' 						=> $row['affiliate_network_mid'], 
								'title' 					=> $row['store_title'], 
								'url_key' 					=> $row['url_key'], 
								'description' 				=> $row['store_description'], 
								'image_url' 				=> $row['image_url'], 
								'affiliate_network' 		=> $row['affiliate_network'], 
								'affiliate_network_link'	=> $row['affiliate_network_link'], 
								'store_base_currency' 		=> $row['store_base_currency'], 
								'store_base_country' 		=> $row['store_base_country'], 
								'category_ids' 				=> $row['category_ids'], 
								'date' 						=> $date
							) 
						);
					} else 
					{
						$wpdb->update( 
							$stores_table, 
							array( 
								'mid' 						=> $row['affiliate_network_mid'], 
								'title' 					=> $row['store_title'], 
								'url_key' 					=> $row['url_key'], 
								'description' 				=> $row['store_description'], 
								'image_url' 				=> $row['image_url'], 
								'affiliate_network' 		=> $row['affiliate_network'], 
								'affiliate_network_link'	=> $row['affiliate_network_link'], 
								'store_base_currency' 		=> $row['store_base_currency'], 
								'store_base_country' 		=> $row['store_base_country'], 
								'category_ids' 				=> $row['category_ids'],
								'date' 						=> $date
							),
							array( 'rg_store_id' => $rg_store_exists )
						);
					}										
				}
			} else 
			{
				$string .= '<p style="color:red">'.$resp_from_server['response']['message'].'</p>';
			}
		} else if( $import_type == 'rg_categories_import' )
		{
			rg_update_subscription_expiry_date($subscriptionid, $userpassword, $useremail, $projectid);
			$projectType =	rg_project_type();
			if($projectType=="Free"){
				$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( RGPFEEDS_API_URL . "partner/product_categories/json/".$project_detail[0]->subcription_id, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 
			}else{
				$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( RGPFEEDS_API_URL . "api/deal_categories/json/".$project_detail[0]->subcription_id, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 
			}
			$resultCategories = $resp_from_server['response']['categories'];
			if($resp_from_server['response']['success'] == 1 )
			{
				foreach($resultCategories as $row)
				{	
					$sqlincat = "Select rg_category_id FROM $categories_table Where rg_category_id = '".$row['productfeeds_category_id']."'";
					$rg_category_exists = $wpdb->get_var( $sqlincat );
					if( empty( $rg_category_exists ) )
					{					
						$title 		= $row['productfeeds_cateogry_title'];
						$url_key 	= preg_replace('/[^\w\d_ -]/si', '', $title); 	// remove any special character
						$url_key 	= preg_replace('/\s\s+/', ' ', $url_key);		// replacing multiple spaces to signle
						$url_key 	= strtolower(str_replace(" ","-",$url_key));
						$wpdb->insert( 
							$categories_table, 
							array( 
								'rg_category_id' 		=> $row['productfeeds_category_id'], 
								'title' 				=> $row['productfeeds_cateogry_title'], 
								'url_key' 				=> $url_key, 
								'parent' 				=> $row['parent_category_id'], 
								'date' 					=> date('Y-m-d H:i:s')
							) 
						);
					} else 
					{
						$title 		= $row['productfeeds_cateogry_title'];
						$url_key 	= preg_replace('/[^\w\d_ -]/si', '', $title); 	// remove any special character
						$url_key 	= preg_replace('/\s\s+/', ' ', $url_key);		// replacing multiple spaces to signle
						$url_key 	= strtolower(str_replace(" ","-",$url_key));
						$wpdb->update( 
							$categories_table, 
							array( 
								'title' 				=> $row['productfeeds_cateogry_title'], 
								'url_key' 				=> $url_key, 
								'parent' 				=> $row['parent_category_id']
							),
							array( 'rg_category_id' => $rg_category_exists )
						);
					}
				}
				$catQuery = "SELECT * FROM $categories_table ";
				$CateIDs = $wpdb->get_results( $catQuery ); 
				foreach ($CateIDs as $key => $cID) {
								$update_array = array();
								if($cID->parent == '0'){
									$update_array['header_category_tag'] = 'yes';
									$catid = $cID->rg_category_id;
								}else{
									$catid = $cID->parent;
								}
								$update_array['icon_url'] = $catid;
								$update_array['image_url'] = $catid;
								if($key < 12 && $catid != 20){
									$update_array['popular_category_tag'] = 'yes';
								}
								$wpdb->update( 
										$categories_table, 
										$update_array,
										array( 'rg_category_id' => $cID->rg_category_id )
									); 
								}
			} else 
			{
				$string .= '<p style="color:red">'.$resp_from_server['response']['message'].'</p>';
			}
		} 
	} else 
	{
		$string .= "<p style='color:red'>Please subscribe for your RevGlue project first, then you have the facility to import the data";
	}
	$response_array = array();
	$response_array['error_msgs'] = $string;
	$sql_1 = "SELECT MAX(date) FROM $categories_table";
	$last_updated_category = $wpdb->get_var($sql_1);
	$response_array['last_updated_category'] = ( $last_updated_category ? date( 'l , d-M-Y , h:i A', strtotime( $last_updated_category ) ) : '-' );
	$sql = "SELECT MAX(date) FROM $stores_table";
	$last_updated_store = $wpdb->get_var($sql);
	$response_array['last_updated_store'] = ( $last_updated_store ? date( 'l , d-M-Y , h:i A', strtotime( $last_updated_store ) ) : '-' );
	$sql_2 = "SELECT count(*) as categories FROM $categories_table";
	$count_category = $wpdb->get_results($sql_2);
	$response_array['count_category'] = $count_category[0]->categories;
	$sql_3 = "SELECT count(*) as stores FROM $stores_table";
	$count_store = $wpdb->get_results($sql_3);
	$response_array['count_store'] = $count_store[0]->stores;
	echo json_encode($response_array);
	wp_die();
}
add_action( 'wp_ajax_revglue_pfeeds_data_import', 'revglue_pfeeds_data_import' );
function import_revbanner_data()
{
	global $wpdb;
	$project_table = $wpdb->prefix.'rg_projects';
	$banner_table = $wpdb->prefix.'rg_banner';
	$string = '';
	$import_type = sanitize_text_field( $_POST['import_type'] );
	$sql = "SELECT *FROM $project_table where project like 'Banners UK'";
	$project_detail = $wpdb->get_results($sql);
	$rows = $wpdb->num_rows;
	if( !empty ( $rows ) )
	{
		if( $import_type == 'rg_banners_import' )
		{
			$i = 0;
			$page = 1;
			do {
				$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( RGPFEEDS_API_URL . "api/banners/json/".$project_detail[0]->subcription_id."/".$page, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 
				update_option("rg_banners_status", $page);
				$total = ceil( $resp_from_server['response']['banners_total'] / 1000 ) ;
				$result = $resp_from_server['response']['banners'];
				if($resp_from_server['response']['success'] == true )
				{
					foreach($result as $row)
					{
						// pre($result);
						// die();
						$sqlinstore = "Select rg_store_banner_id FROM $banner_table Where rg_store_banner_id = '".$row['rg_banner_id']."' AND `banner_type` = 'imported'";
						$rg_banner_exists = $wpdb->get_var( $sqlinstore );
						if( empty( $rg_banner_exists ) )
						{
							$wpdb->insert( 
								$banner_table, 
								array( 
					'rg_store_banner_id' 	=> $row['rg_banner_id'], 
					'rg_store_id' 			=> $row['rg_store_id'], 
					'title' 				=> $row['banner_alt_text'], 
					'rg_store_name' 		=> $row['banner_alt_text'], 
					'image_url' 			=> $row['banner_image_url'], 
					'url' 					=> $row['deep_link'], 
					'rg_size' 			    => $row['width_pixels'].'x'.$row['height_pixels'], 
					'placement' 			=> 'unassigned', 
					'banner_type' 			=> 'imported'
								) 
							);
						} else 
						{
							$wpdb->update( 
								$banner_table, 
								array( 
									'rg_store_id' 			=> $row['rg_store_id'], 
									'title' 				=> $row['banner_alt_text'], 
									'rg_store_name' 		=> $row['banner_alt_text'], 
									'url' 					=> $row['deep_link'], 
									'image_url' 			=> $row['banner_image_url']	
								),
								array( 'rg_store_banner_id' => $rg_banner_exists )
							);
						}										
					}
				} else 
				{
					$string .= '<p style="color:red">'.$resp_from_server['response']['message'].'</p>';
				}
				$i++;
				$page++;
			} while ( $i < $total );
		}
	} else 
	{
		$string .= "<p style='color:red'>Please subscribe for your RevGlue project first, then you have the facility to import the data";
	}
	$response_array = array();
	$response_array['error_msgs'] = $string;
	$sql1 = "SELECT count(*) as banner FROM $banner_table where banner_type= 'imported'";
	$count_banner = $wpdb->get_results($sql1);
	$response_array['count_banner'] = $count_banner[0]->banner;
	echo json_encode($response_array);
	wp_die();
}
add_action( 'wp_ajax_import_revbanner_data', 'import_revbanner_data' );
function revglue_pfeeds_data_delete()
{
	global $wpdb;
	$stores_table = $wpdb->prefix.'rg_stores';
	$categories_table = $wpdb->prefix.'rg_categories';
	$banner_table = $wpdb->prefix.'rg_banner';
	$data_type = sanitize_text_field( $_POST['data_type'] );
	$response_array = array();
	if( $data_type == 'rg_stores_delete' )
	{
		$response_array['data_type'] = 'rg_stores';
		$wpdb->query( "DELETE FROM $stores_table" );	
		$sql = "SELECT MAX(date) FROM $stores_table";
		$last_updated_store = $wpdb->get_var($sql);
		$response_array['last_updated_store'] = ( $last_updated_store ? date( 'l , d-M-Y , h:i A', strtotime( $last_updated_store ) ) : '-' );
		$sql2 = "SELECT count(*) as stores FROM $stores_table";
		$count_store = $wpdb->get_results($sql2);
		$response_array['count_store'] = $count_store[0]->stores;
	} else if( $data_type == 'rg_categories_delete' )
	{
		$response_array['data_type'] = 'rg_categories';
		$wpdb->query( "DELETE FROM $categories_table" );	
		$sql = "SELECT MAX(date) FROM $categories_table";
		$last_updated_category = $wpdb->get_var($sql);
		$response_array['last_updated_category'] = ( $last_updated_category ? date( 'l , d-M-Y , h:i A', strtotime( $last_updated_category ) ) : '-' );
		$sql2 = "SELECT count(*) as categories FROM $categories_table";
		$count_category = $wpdb->get_results($sql2);
		$response_array['count_category'] = $count_category[0]->categories;
	} else if( $data_type == 'rg_banners_delete' )
	{
		$response_array['data_type'] = 'rg_banners';
		$wpdb->query( "DELETE FROM $banner_table where banner_type='imported'" );	
		$sql1 = "SELECT count(*) as banner FROM $banner_table where banner_type= 'imported'";
		$count_banner = $wpdb->get_results($sql1);
		$response_array['count_banner'] = $count_banner[0]->banner;
	} 
	echo json_encode($response_array);
	wp_die();
}
add_action( 'wp_ajax_revglue_pfeeds_data_delete', 'revglue_pfeeds_data_delete' );
function revglue_pfeeds_update_home_store()
{
	global $wpdb; 
	$stores_table = $wpdb->prefix.'rg_stores';
	$store_id = absint( $_POST['store_id'] );
	$cat_state 	= sanitize_text_field( $_POST['state'] );
	$wpdb->update( 
		$stores_table, 
		array( 'homepage_store_tag' => $cat_state ), 
		array( 'rg_store_id' => $store_id )
	);
	echo $store_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_pfeeds_update_home_store', 'revglue_pfeeds_update_home_store' );
function revglue_pfeeds_update_header_category()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$cat_state 	= sanitize_text_field( $_POST['state'] );
	$wpdb->update( 
		$categories_table, 
		array( 'header_category_tag' => $cat_state ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_pfeeds_update_header_category', 'revglue_pfeeds_update_header_category' );
function revglue_pfeeds_update_popular_category()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$cat_state 	= sanitize_text_field( $_POST['state'] );
	$wpdb->update( 
		$categories_table, 
		array( 'popular_category_tag' => $cat_state ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_pfeeds_update_popular_category', 'revglue_pfeeds_update_popular_category' );
function revglue_pfeeds_update_category_icon()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$icon_url 	= esc_url_raw( $_POST['icon_url'] );
	$wpdb->update( 
		$categories_table, 
		array( 'icon_url' => $icon_url ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_pfeeds_update_category_icon', 'revglue_pfeeds_update_category_icon' );
function revglue_pfeeds_delete_category_icon()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$wpdb->update( 
		$categories_table, 
		array( 'icon_url' => '' ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_pfeeds_delete_category_icon', 'revglue_pfeeds_delete_category_icon' );
function revglue_pfeeds_update_category_image()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$image_url 	= esc_url_raw( $_POST['image_url'] );
	$wpdb->update( 
		$categories_table, 
		array( 'image_url' => $image_url ),
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_pfeeds_update_category_image', 'revglue_pfeeds_update_category_image' );
function revglue_pfeeds_delete_category_image()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$wpdb->update( 
		$categories_table, 
		array( 'image_url' => '' ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_pfeeds_delete_category_image', 'revglue_pfeeds_delete_category_image' );
function fetch_deals($subcriptionid,$projectid, $rg_store_id,$page=1,$pfeeds_table,$wpdb,$date){
						$projectType =	rg_project_type();
						if( $projectType=="Free"){
							$apiurl = "https://www.revglue.com/partner/productdata/$rg_store_id/$projectid/json/$subcriptionid/$page" ;	
							$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( $apiurl, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 
						}else{
							$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( RGPFEEDS_API_URL . "api/deals/json/".$subcriptionid."/".$rg_store_id."/".$page, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 
						}
						//  pre($resp_from_server['response']);
						//  die;
						$total = (int) $resp_from_server['response']['total_deals'];
						$total_pages = (int) $resp_from_server['response']['total_pages'];
						$total= $total;
						$result = $resp_from_server['response']['deals'];
						// pre($result);
						// die;
						if($resp_from_server['response']['success'] == true )
						{
							foreach($result as $key => $row)
							{ 
								set_time_limit(0);
								// pre("come here 1");
								/*if($key == 999 && $total>$page){
									$page++;
									fetch_deals($project_detail,$projectid,$rg_store_id,$page,$pfeeds_table,$wpdb,$date);
								}*/
								$exist = $wpdb->get_var("SELECT count(*) FROM $pfeeds_table WHERE rg_deal_id='$row[productfeed_id]'");
								// pre("come here 2");
								$productfeed_id = $row['productfeed_id'];
								$rg_store_id = $row['rg_store_id'];
								$productfeed_title = addslashes($row['productfeed_title']);
								$productfeed_description = addslashes($row['productfeed_description']);
								$image_url = $row['image_url'];
								$deep_link = $row['deep_link'];
								$brand = $row['brand'];
								$price = $row['price'];
								$rrp = $row['rrp'];
								$discount = 0;
								if($rrp > 0){
									$discount = round((($rrp-$price)/$rrp)*100);
								}
								

								$productfeeds_category_id = $row['productfeeds_category_id'];
								$date = $date;
								if($exist == 0){
				$wpdb->insert( 
					$pfeeds_table, 
						array( 
							'rg_deal_id'			=> $productfeed_id,
							'rg_store_id'			=> $rg_store_id,
							'title'				    => $productfeed_title,
							'description'			=> $productfeed_description,
							'image_url'				=> $image_url,
							'deeplink'				=> $deep_link,
							'brand'					=> $brand,
							'price'					=> $price,
							'rrp'					=> $rrp,
							'discount'				=> $discount,
							'category_ids'			=> $productfeeds_category_id,
							'date'					=> $date
						) 
					);
				}else{
					// pre("come here 3");
						// die();
					$wpdb->update( 
						$pfeeds_table, 
						array(
							'rg_store_id'			=> $rg_store_id,
							'title'				    => $productfeed_title,
							'description'			=> $productfeed_description,
							'image_url'				=> $image_url,
							'deeplink'				=> $deep_link,
							'brand'					=> $brand,
							'price'					=> $price,
							'rrp'					=> $rrp,
							'discount'				=> $discount,
							'category_ids'			=> $productfeeds_category_id,
							'date'					=> $date
						),
					array( 'rg_deal_id' => $productfeed_id )
									);
					// echo $wpdb->last_query;
							// die();
								}										
							}
							if(  $total_pages>$page){
									$page++;
									  // echo $page;
									  // die;
									fetch_deals($project_detail,$projectid,$rg_store_id,$page,$pfeeds_table,$wpdb,$date);
									 //die("here");

								}
									// pre($page);

								if($total_pages== $page){
									// die("here");

									$wpdb->query( "DELETE FROM $pfeeds_table WHERE `date` != '$date' AND `rg_store_id` = $rg_store_id " );
								}

							// echo $wpdb->last_query;
									// die();
							/*$wpdb->query( "DELETE FROM $pfeeds_table WHERE `date` != '$date' AND `rg_store_id` = $rg_store_id " );*/
						} else 
						{
							$string = '<p style="color:red">'.$resp_from_server['response']['message'].'</p>';
						}
				}
function revglue_pfeeds_get_product_feeds()
{
	global $wpdb;
	$rg_store_id = absint( $_POST['rg_store_id'] );
	$project_table = $wpdb->prefix.'rg_projects';
	$pfeeds_table = $wpdb->prefix.'rg_pfeeds';
	$string = '';
	$date  = date('Y-m-d');
	$sql = "SELECT * FROM $project_table WHERE project LIKE 'Product Feeds UK'";
	$project_detail = $wpdb->get_row($sql, ARRAY_A);
	$subcriptionid =	$project_detail['subcription_id'];
	$projectid ='';
	$rows = $wpdb->num_rows;
	if(  $rows > 0 )
	{
				$projectid =	$project_detail['partner_iframe_id'];
				$page = 1;
				fetch_deals($subcriptionid,$projectid,$rg_store_id,$page,$pfeeds_table,$wpdb,$date); 
	}
	$response_array = array(); 
	$response_array['error_msgs'] = $string;
	$response_array['rg_store_id'] = $rg_store_id;
	$sql1  = "SELECT COUNT( rg_store_id ) as total_feeds, MAX( date ) as last_updated FROM $pfeeds_table WHERE rg_store_id = $rg_store_id";
	$project_detail = $wpdb->get_results($sql1);
	$sqld = "SELECT MAX(date) FROM $pfeeds_table WHERE rg_store_id = $rg_store_id";
	$last_updated_feed = $wpdb->get_var($sqld);
	$response_array['last_updated_feed'] = ( $last_updated_feed ? date("d-M-Y", strtotime( $last_updated_feed )) : '-' );
	$sqlc = "SELECT COUNT(*) FROM $pfeeds_table WHERE rg_store_id = $rg_store_id";
	$count_feed = $wpdb->get_var($sqlc);
	$response_array['count_feed'] = $count_feed;
	echo json_encode($response_array);
	wp_die();
}
add_action( 'wp_ajax_revglue_pfeeds_get_product_feeds', 'revglue_pfeeds_get_product_feeds' );
function revglue_pfeeds_load_banners()
{
	global $wpdb; 
	$stores_table = $wpdb->prefix.'rg_stores';
	$sTable = $wpdb->prefix.'rg_banner';
	$upload = wp_upload_dir();
	$base_url = $upload['baseurl'];
	$uploadurl = $base_url.'/revglue/product-feeds/banners/';
	$placements = array(
		'home-top'				=> 'Home:: Top Header',
		'home-slider'			=> 'Home:: Main Banners',
		'home-mid'				=> 'Home:: After Categories',
		'home-bottom'			=> 'Home:: Before Footer',
		'cat-top'				=> 'Category:: Top Header',
		'cat-side-top'			=> 'Category:: Top Sidebar',
		'cat-side-bottom'		=> 'Category:: Bottom Sidebar 1',
		'cat-side-bottom-two'	=> 'Category:: Bottom Sidebar 2',
		'cat-bottom'			=> 'Category:: Before Footer',
		'store-top'				=> 'Store:: Top Header',
		'store-side-top'		=> 'Store:: Top Sidebar',
		'store-side-bottom'		=> 'Store:: Bottom Sidebar 1',
		'store-side-bottom-two'	=> 'Store:: Bottom Sidebar 2',
		'store-main-bottom'		=> 'Store:: After Review',
		'store-bottom'			=> 'Store:: Before Footer',
		'unassigned' 			=> 'Unassigned Banners'
	);
	$aColumns = array( 'banner_type', 'placement', 'status', 'title', 'url', 'image_url', 'rg_store_id', 'rg_id', 'rg_store_banner_id', 'rg_store_name', 'rg_size'  ); 
	$sIndexColumn = "rg_store_id"; 
	$sLimit = "LIMIT 1, 50"; 
	

	if ( isset( $_REQUEST['start'] ) && sanitize_text_field($_REQUEST['length'])  != '-1' )
	{
		$sLimit = "LIMIT ".intval( sanitize_text_field($_REQUEST['start']) ).", ".intval( sanitize_text_field($_REQUEST['length'])  );
	}
	$sOrder = "";
	// make order functionality
	$where = "";
	$globalSearch = array();
	$columnSearch = array();
	$dtColumns = $aColumns;
	if ( isset($_REQUEST['search']) && sanitize_text_field($_REQUEST['search']['value']) != '' ) {
		$str = sanitize_text_field($_REQUEST['search']['value']);
		$request_columns = [];
		foreach ($_REQUEST['columns'] as $key => $val ) {
			if(is_array($val)){$request_columns[$key] = $val;}
			else{$request_columns[$key] = sanitize_text_field($val);}
		}

		for ( $i=0, $ien=count($request_columns ) ; $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($request_columns[$i]) ;

			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}

		/*for ( $i=0, $ien=count($_REQUEST['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $_REQUEST['columns'][$i];
			$column = $dtColumns[ $requestColumn['data'] ];
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}*/
	}
	// Individual column filtering
	if ( isset( $_REQUEST['columns'] ) ) {
		$request_columns = [];
		foreach ($_REQUEST['columns'] as $key => $val ) {
			if(is_array($val)){$request_columns[$key] = $val;}
			else{$request_columns[$key] = sanitize_text_field($val);}
		}

			for ( $i=0, $ien=count($request_columns) ; $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($request_columns[$i]) ;
			//$columnIdx = array_search( $requestColumn['data'], $dtColumns );
			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			$str = sanitize_text_field($requestColumn['search']['value']) ;
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}

		/*for ( $i=0, $ien=count($_REQUEST['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $_REQUEST['columns'][$i];
			//$columnIdx = array_search( $requestColumn['data'], $dtColumns );
			$column = $dtColumns[ $requestColumn['data'] ];
			$str = $requestColumn['search']['value'];
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}*/
	}
	// Combine the filters into a single string
	$where = '';
	if ( count( $globalSearch ) ) {
		$where = '('.implode(' OR ', $globalSearch).')';
	}
	if ( count( $columnSearch ) ) {
		$where = $where === '' ?
			implode(' AND ', $columnSearch) :
			$where .' AND '. implode(' AND ', $columnSearch);
	}
	if ( $where !== '' ) {
		$where = 'WHERE '.$where;
	}
	$sQuery = "SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."` FROM   $sTable $where $sOrder $sLimit";
	$rResult = $wpdb->get_results($sQuery, ARRAY_A);
	$sQuery = "SELECT FOUND_ROWS()";
	$rResultFilterTotal = $wpdb->get_results($sQuery, ARRAY_N); 
	$iFilteredTotal = $rResultFilterTotal [0];
	$sQuery = "SELECT COUNT(`".$sIndexColumn."`) FROM   $sTable";
	$rResultTotal = $wpdb->get_results($sQuery, ARRAY_N); 
	$iTotal = $rResultTotal [0];
	$output = array(
		"draw"            => isset ( $_REQUEST['draw'] ) ? intval( $_REQUEST['draw'] ) : 0,
		"recordsTotal"    => $iTotal,
		"recordsFiltered" => $iFilteredTotal,
		"data"            => array()
	);
	foreach($rResult as $aRow)
	{
		$row = array();
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if( $i == 0 )
			{
				if( $aRow[ $aColumns[5] ] == '' )
				{
					$uploadedbanner = $uploadurl . $aRow[ $aColumns[3] ];
					$row[] = '<div class="revglue-banner-thumb"><img class="revglue-unveil" src="'. RGPFEEDS_PLUGIN_URL .'/admin/images/loading.gif" data-src="'. esc_url( $uploadedbanner ) .'"/></div>';
				} else
				{
					$row[] = '<div class="revglue-banner-thumb"><img class="revglue-unveil" src="'. RGPFEEDS_PLUGIN_URL .'/admin/images/loading.gif" data-src="'. esc_url( $aRow[ $aColumns[5] ] ) .'" /></div>';
				}
			}else if( $i == 1 )
			{
				$row[] = $aRow[ $aColumns[8] ];
			} else if( $i == 2 )
			{
				$row[] = $aRow[ $aColumns[9] ];
			} else if( $i == 3 )
			{
				$row[] = ( $aRow[ $aColumns[0] ] == 'local' ? 'Local' : 'RevGlue Banner' );
			} else if( $i == 4 )
			{
				$row[] = $placements[$aRow[ $aColumns[1]]];
			} else if( $i == 5 )
			{
				$row[] = $aRow[ $aColumns[10]];
			} else if( $i == 6 )
			{
				if( ! empty( $aRow[ $aColumns[4]] ) )
				{
					$url_to_show = esc_url( $aRow[ $aColumns[4]] ); 
				} else if( ! empty( $aRow[ $aColumns[6]] ) )
				{
					$sql_1 = "SELECT affiliate_network_link FROM $stores_table where rg_store_id = ".$aRow[ $aColumns[6]];
					$deep_link = $wpdb->get_results($sql_1);
					$url_to_show = ( !empty( $deep_link[0]->affiliate_network_link ) ? esc_url( $deep_link[0]->affiliate_network_link ) : 'No Link'  );
				} else
				{
					$url_to_show = 'No Link';
				}
					$url_to_show = clean_Deep_Link($url_to_show);
				$row[] = '<a class="rg_store_link_pop_up" id="'. $aRow[ $aColumns[7]] .'" href="'. $url_to_show .'" title="'. $url_to_show .'"  target="_blank"><img src="'. RGPFEEDS_PLUGIN_URL .'/admin/images/linkicon.png" style="width:50px;"/></a>';
			} else if( $i == 7 )
			{
				$row[] = $aRow[ $aColumns[2]];
			} else if( $i == 8 )
			{
				$row[] = '<a href="'. admin_url( 'admin.php?page=revglue-banners&action=edit&banner_id='.$aRow[ $aColumns[7]] ) .'">Edit</a>';
			} else if ( $aColumns[$i] != ' ' )
			{    
				$row[] = $aRow[ $aColumns[$i] ];
			}
		}
		$output['data'][] = $row;
	}
	echo json_encode( $output );
	die(); 
}
add_action( 'wp_ajax_revglue_pfeeds_load_banners', 'revglue_pfeeds_load_banners' );
function revglue_pfeed_load_pfeeds(){
	global $wpdb;
	$sTable = $wpdb->prefix.'rg_stores';
	$pfeeds_table = $wpdb->prefix.'rg_pfeeds';
	$aColumns = array( 'rg_store_id', 'title', 'image_url' );
	$sIndexColumn = "rg_store_id";
	$sLimit = "LIMIT 1, 50";
	if ( isset( $_REQUEST['start'] ) && sanitize_text_field($_REQUEST['length'])  != '-1' )
	{
		$sLimit = "LIMIT ".intval( sanitize_text_field($_REQUEST['start']) ).", ".intval( sanitize_text_field($_REQUEST['length'])  );
	}

	$sOrder = "";
	// make order functionality
	$where = "";
	$globalSearch = array();
	$columnSearch = array();
	$dtColumns = $aColumns;
	if ( isset($_REQUEST['search']) && sanitize_text_field($_REQUEST['search']['value']) != '' ) {
		$str = sanitize_text_field($_REQUEST['search']['value']);

		$request_columns = [];
		foreach ($_REQUEST['columns'] as $key => $val ) {
			if(is_array($val)){$request_columns[$key] = $val;}
			else{$request_columns[$key] = sanitize_text_field($val);}
		}
		for ( $i=0, $ien=count($request_columns) ; $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($request_columns[$i]);

			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}

		/*for ( $i=0, $ien=count($dtColumns); $i<$ien ; $i++ ) {
			$requestColumn = $_REQUEST['columns'][$i];
			$column = $dtColumns[ $requestColumn['data'] ];
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}*/
	}
	 //print_r($globalSearch);
	// Individual column filtering
	if ( isset( $_REQUEST['columns'] ) ) {
		$request_columns = [];
		foreach ($_REQUEST['columns'] as $key => $val ) {
			if(is_array($val)){$request_columns[$key] = $val;}
			else{$request_columns[$key] = sanitize_text_field($val);}
		}
		for ( $i=0, $ien=count($request_columns) ; $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($request_columns[$i]) ;
			//$columnIdx = array_search( $requestColumn['data'], $dtColumns );
			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			$str = sanitize_text_field($requestColumn['search']['value']) ;
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}

		/*for ( $i=0, $ien=count($dtColumns); $i<$ien ; $i++ ) {
			$requestColumn = $_REQUEST['columns'][$i];
			$column = $dtColumns[ $requestColumn['data'] ];
			$str = $requestColumn['search']['value'];
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}*/
	}
	// Combine the filters into a single string
	$where = '';
	if ( count( $globalSearch ) ) {
		$where = '('.implode(' OR ', $globalSearch).')';
	}
	if ( count( $columnSearch ) ) {
		$where = $where === '' ?
			implode(' AND ', $columnSearch) :
			$where .' AND '. implode(' AND ', $columnSearch);
	}
	if ( $where !== '' ) {
		$where = 'WHERE '.$where;
	} 
	$sQuery  = "SELECT * FROM $sTable $where $sOrder $sLimit";
	$sQuery1  = "SELECT count(*) as total FROM $sTable $where";
	// die($sQuery);
	/*$sQuery .= "LEFT JOIN $pfeeds_table ON $sTable.rg_store_id = $pfeeds_table.rg_store_id ";
	$sQuery .= "GROUP BY $sTable.rg_store_id ORDER BY $sTable.title ";*/
	$rResult = $wpdb->get_results($sQuery, ARRAY_A);
	$sqld = "SELECT rg_store_id, MAX( `date` ) as last_updated, count(rg_store_id) as total 
		FROM $pfeeds_table group by rg_store_id";
		$pfeedDate = $wpdb->get_results($sqld);
		$feedarray = array();
		foreach($pfeedDate as $row){
				$feedarray[$row->rg_store_id]['last_updated'] = $row->last_updated;
				$feedarray[$row->rg_store_id]['total'] = $row->total;
		}
	$sQuery = "SELECT FOUND_ROWS()";
	// $rResultFilterTotal = $wpdb->get_results($sQuery, ARRAY_N); 
	$rResultFilterTotal = $wpdb->get_results($sQuery1, ARRAY_N); 
	$iFilteredTotal = $rResultFilterTotal [0];
	$sQuery = "SELECT COUNT(`".$sIndexColumn."`) FROM   $sTable";
	$rResultTotal = $wpdb->get_results($sQuery, ARRAY_N); 
	$iTotal = $rResultTotal [0];
	$output = array(
		"draw"            => isset ( $_REQUEST['draw'] ) ? intval( sanitize_text_field($_REQUEST['draw']) ) : 0,
		"recordsTotal"    => $iTotal,
		"recordsFiltered" => $iFilteredTotal,
		"data"            => array()
	);
	foreach($rResult as $aRow)
	{
		$row = array();
		for ( $i=0 ; $i<6 ; $i++ )
		{
			if( $i == 0 )
			{
				$row[] = '<div class="revglue-banner-thumb"><img class="revglue-unveil"
				 src="'. RGPFEEDS_PLUGIN_URL.'/admin/images/loading.gif"
				  data-src="'. $aRow[ $aColumns[2] ].'"  /></div>';
			} else if( $i == 1 )
			{
				  $row[] = $aRow[ $aColumns[0] ];
			} else if( $i == 2 )
			{
				$row[] = $aRow[ $aColumns[1] ];
			} else if( $i == 3 )
			{
					$uDAte =   @date("d-M-Y", strtotime($feedarray[$aRow[ $aColumns[0] ]]['last_updated'] ));
					$uDAte = ($uDAte != "01-Jan-1970") ? $uDAte  : "-"	 .'</div>';  
				 $row[]='<div id="pfeed_updated_'.$aRow[ $aColumns[0] ].'">
						 '.$uDAte;
			} else if( $i == 4 )
			{
				$countofFeed =  $feedarray[$aRow[ $aColumns[0] ]]['total'] ? $feedarray[$aRow[ $aColumns[0] ]]['total']  : 0 ;
				 $row[] = '<span id="pfeed_fcount_'.$aRow[ $aColumns[0] ].'">'.$countofFeed.'</span>';
			} else if( $i == 5 )
			{ 
				$row[]='<div id="pfeed_antiloader_'. $aRow[ $aColumns[0] ] .'" class="pfeed_antiloader">
								<a href="javascript:" class="rg_import_pfeed btn btn-primary txtwhite" data-rg_store_id="'. $aRow[ $aColumns[0] ] .'" >Import</a>
							</div>
							<div id="pfeed_loader_'.$aRow[ $aColumns[0] ].'" style="display:none">
							<img src=" '.RGPFEEDS_PLUGIN_URL .'/admin/images/loading.gif" />
							<br/>
							Importing - please wait... 
							</div>'	;
			}     
		}
		$output['data'][] = $row;
	}
	echo json_encode( $output );
	die();   
}
add_action( 'wp_ajax_revglue_pfeed_load_pfeeds', 'revglue_pfeed_load_pfeeds' );
function revglue_pfeed_load_stores(){
	global $wpdb;
	$stores_table = $wpdb->prefix.'rg_stores'; 
	$aColumns = array( 'rg_store_id', 'affiliate_network', 'mid', 'image_url','title','store_base_country','affiliate_network_link','homepage_store_tag' );
	$sIndexColumn = "rg_store_id";
	$sLimit = "LIMIT 1, 50"; 
		if ( isset( $_REQUEST['start'] ) && sanitize_text_field($_REQUEST['length']) != '-1' )
	
	{
		$sLimit = "LIMIT ".intval(sanitize_text_field($_REQUEST['start'])).", ".intval(sanitize_text_field($_REQUEST['length']));
	}

	$sOrder = "";
	// make order functionality
	$where = "";
	$globalSearch = array();
	$columnSearch = array();
	$dtColumns = $aColumns;
	if ( isset($_REQUEST['search']) && sanitize_text_field($_REQUEST['search']['value']) != '' ) {
		$str = sanitize_text_field($_REQUEST['search']['value']);
		for ( $i=0, $ien=count($dtColumns); $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($_REQUEST['columns'][$i]) ;
			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}
	}
	// Individual column filtering
	if ( isset( $_REQUEST['columns'] ) ) {
		for ( $i=0, $ien=count($dtColumns); $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($_REQUEST['columns'][$i]);
			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			$str = sanitize_text_field($requestColumn['search']['value']) ;
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}
	}
	// Combine the filters into a single string
	$where = '';
	if ( count( $globalSearch ) ) {
		$where = '('.implode(' OR ', $globalSearch).')';
	}
	if ( count( $columnSearch ) ) {
		$where = $where === '' ?
			implode(' AND ', $columnSearch) :
			$where .' AND '. implode(' AND ', $columnSearch);
	}
	if ( $where !== '' ) {
		$where = 'WHERE '.$where;
	} 
	$sQuery  = " SELECT * FROM $stores_table $where $sOrder $sLimit";
	$sQuery1  = "SELECT count(*) as total FROM $stores_table $where";
	//die($sQuery);
	$rResult = $wpdb->get_results($sQuery, ARRAY_A);
	//$sQuery = "SELECT FOUND_ROWS() * from $stores_table";
	$sQuery = "SELECT FOUND_ROWS() ";
	$rResultFilterTotal = $wpdb->get_results($sQuery1, ARRAY_N); 
	$iFilteredTotal = $rResultFilterTotal [0];
	$sQuery = "SELECT COUNT(`".$sIndexColumn."`) FROM   $stores_table";
	$rResultTotal = $wpdb->get_results($sQuery, ARRAY_N); 
	$iTotal = $rResultTotal [0];
	$output = array(
		"draw"            => isset ( $_REQUEST['draw'] ) ? intval( $_REQUEST['draw'] ) : 0,
		"recordsTotal"    => $iTotal,
		"recordsFiltered" => $iFilteredTotal,
		"data"            => array()
	);
	foreach($rResult as $aRow)
	{
		$row = array();
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if( $i == 0 )
			{
				$row[] = $aRow[ $aColumns[$i] ];
			} else if( $i == 1 )
			{
				$row[] = $aRow[ $aColumns[$i] ];
			} else if( $i == 2 )
			{
				$row[] = $aRow[ $aColumns[$i] ];
			} else if( $i == 3 )
			{
			 	$row[]='<div class="revglue-banner-thumb"><img class="revglue-unveil" src="'. RGPFEEDS_PLUGIN_URL.'/admin/images/loading.gif" data-src="'.$aRow[ $aColumns[3] ].'" />
							</div>';
			} else if( $i == 4 )
			{
				 	$row[] = $aRow[ $aColumns[$i] ]; 
			} else if( $i == 5 )
			{ 
				$row[] = $aRow[ $aColumns[$i] ];
			} else if( $i == 6 )
			{ 
				 $deeplink =  $aRow[ $aColumns[$i] ];
				 $deeplink = clean_Deep_Link($deeplink);
				$row[]='<a class="rg_store_link_pop_up" title="'. $deeplink .'" id="'. $aRow[ $aColumns[0] ] .'" href="'.$deeplink.'"  target="_blank">
								<img src="'. RGPFEEDS_PLUGIN_URL.'/admin/images/linkicon.png" style="width:50px;"/>
								</a>'; 
			} else if( $i == 7 )
			{ 
				if( $aRow[ $aColumns[$i] ] == 'yes' )
							{
								$checked = 'checked="checked"';
							} else
							{
								$checked = '';
							}
				$row[] = '<input  '. $checked .' type="checkbox" id="'.  $aRow[ $aColumns[0] ] .'" class="rg_store_homepage_tag" />'; 
			}    
		}
		$output['data'][] = $row;
	}
	echo json_encode( $output );
	die();   
}
add_action( 'wp_ajax_revglue_pfeed_load_stores', 'revglue_pfeed_load_stores' );
function revglue_pfeed_load_listing(){
	global $wpdb;
	$pfeeds_table = $wpdb->prefix.'rg_pfeeds';
	$aColumns = array( 'title', 'description','image_url', 'deeplink','price','rrp','category_ids','status' );
	$sIndexColumn = "rg_store_id";
	$sLimit = "LIMIT 1, 50"; 
		if ( isset( $_REQUEST['start'] ) && sanitize_text_field($_REQUEST['length']) != '-1' )
	
	{
		$sLimit = "LIMIT ".intval(sanitize_text_field($_REQUEST['start'])).", ".intval(sanitize_text_field($_REQUEST['length']));
	}
	$sOrder = "";
	// make order functionality
	$where = "";
	$globalSearch = array();
	$columnSearch = array();
	$dtColumns = $aColumns;
	if ( isset($_REQUEST['search']) && sanitize_text_field($_REQUEST['search']['value']) != '' ) {
		$str = sanitize_text_field($_REQUEST['search']['value']) ;
		for ( $i=0, $ien=count($dtColumns); $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($_REQUEST['columns'][$i]) ;
			$column = $_REQUEST['columns'][$i]($dtColumns[ $requestColumn['data'] ]) ;
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}
	}
	// Individual column filtering
	if ( isset( $_REQUEST['columns'] ) ) {
		for ( $i=0, $ien=count($dtColumns); $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($_REQUEST['columns'][$i]) ;
			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			$str = sanitize_text_field($requestColumn['search']['value']);
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}
	}
	// Combine the filters into a single string
	$where = '';
	if ( count( $globalSearch ) ) {
		$where = '('.implode(' OR ', $globalSearch).')';
	}
	if ( count( $columnSearch ) ) {
		$where = $where === '' ?
			implode(' AND ', $columnSearch) :
			$where .' AND '. implode(' AND ', $columnSearch);
	}
	if ( $where !== '' ) {
		$where = 'WHERE '.$where;
	} 
	$sQuery  = " SELECT * FROM $pfeeds_table $where $sOrder $sLimit";
	$rResult = $wpdb->get_results($sQuery, ARRAY_A);
	$sQuery1  = " SELECT count(*) FROM $pfeeds_table $where";
	//die();
	$sQuery = "SELECT FOUND_ROWS()";
	$rResultFilterTotal = $wpdb->get_results($sQuery1, ARRAY_N); 
	$iFilteredTotal = $rResultFilterTotal [0];
	$sQuery = "SELECT COUNT(`".$sIndexColumn."`) FROM   $pfeeds_table";
	$rResultTotal = $wpdb->get_results($sQuery, ARRAY_N); 
	$iTotal = $rResultTotal [0];
	$output = array(
		"draw"            => isset ( $_REQUEST['draw'] ) ? intval( $_REQUEST['draw'] ) : 0,
		"recordsTotal"    => $iTotal,
		"recordsFiltered" => $iFilteredTotal,
		"data"            => array()
	);
	foreach($rResult as $aRow)
	{
		$row = array();
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if( $i == 0 )
			{
				$titleDesc ="";
				$titleDesc .=  '<p>'.$aRow[ $aColumns[$i] ].' </p>';
				// $titleDesc .=  '<p>'.$aRow[ $aColumns[1] ].' </p>';
				$row[] = $titleDesc;
			} else if( $i == 1 )
			{
				//$row[] = $aRow[ $aColumns[$i] ];
				$row[] ='<div class="revglue-banner-thumb"><img  class="revglue-unveil" src="'. RGPFEEDS_PLUGIN_URL.'/admin/images/loading.gif" data-src="'.$aRow[ $aColumns[2] ] .'" /></div>';
			} else if( $i == 2 )
			{
				//$row[] = $aRow[ $aColumns[$i] ];
				$deeplink = $aRow[ $aColumns[3]];
				$clean_Deep_Link =clean_Deep_Link($deeplink);
				$row[]='<a class="rg_store_link_pop_up" title="'. $clean_Deep_Link.'" id="'. $aRow[ $aColumns[0] ] .'" href="'.$clean_Deep_Link.'" title="'.$clean_Deep_Link.'" target="_blank">
								<img src="'. RGPFEEDS_PLUGIN_URL.'/admin/images/linkicon.png" style="width:50px;"/> 
								</a>';
			} else if( $i == 3 )
			{
			 	$row[] = "£".$aRow[ $aColumns[4] ];
			} else if( $i == 4 )
			{
				 	$row[] = "£".$aRow[ $aColumns[5] ]; 
			} else if( $i == 5 )
			{ 
				global $wpdb;
				$category_table = $wpdb->prefix ."rg_categories";
				$catid = $aRow[ $aColumns[6] ];
				$catname =	$wpdb->get_var("SELECT title FROM  $category_table WHERE `rg_category_id` = $catid ");
				$row[] = $catname;
			} else if( $i == 6 )
			{ 
				$row[] = $aRow[ $aColumns[7] ];
			}     
		}
		$output['data'][] = $row;
	}
	echo json_encode( $output );
	die();   
}
add_action( 'wp_ajax_revglue_pfeed_load_listing', 'revglue_pfeed_load_listing' );
function revglue_store_data_delete()
{
	global $wpdb;
	$stores_table = $wpdb->prefix.'rg_stores';
	$categories_table = $wpdb->prefix.'rg_categories';
	$banner_table = $wpdb->prefix.'rg_banner';
	$data_type = sanitize_text_field( $_POST['data_type'] );
	$response_array = array();
	if( $data_type == 'rg_stores_delete' )
	{
		$response_array['data_type'] = 'rg_stores';
		$wpdb->query( "DELETE FROM $stores_table" );	
		$sql = "SELECT MAX(date) FROM $stores_table";
		$last_updated_store = $wpdb->get_var($sql);
		$response_array['last_updated_store'] = ( $last_updated_store ? date( 'l jS \of F Y h:i:s A', strtotime( $last_updated_store ) ) : '-' );
		$sql2 = "SELECT count(*) as stores FROM $stores_table";
		$count_store = $wpdb->get_results($sql2);
		$response_array['count_store'] = $count_store[0]->stores;
	} else if( $data_type == 'rg_categories_delete' )
	{
		$response_array['data_type'] = 'rg_categories';
		$wpdb->query( "DELETE FROM $categories_table" );	
		$sql = "SELECT MAX(date) FROM $categories_table";
		$last_updated_category = $wpdb->get_var($sql);
		$response_array['last_updated_category'] = ( $last_updated_category ? date( 'l jS \of F Y h:i:s A', strtotime( $last_updated_category ) ) : '-' );
		$sql2 = "SELECT count(*) as categories FROM $categories_table";
		$count_category = $wpdb->get_results($sql2);
		$response_array['count_category'] = $count_category[0]->categories;
	} else if( $data_type == 'rg_banners_delete' )
	{
		$response_array['data_type'] = 'rg_banners';
		$wpdb->query( "DELETE FROM $banner_table where banner_type='imported'" );	
		$sql1 = "SELECT count(*) as banner FROM $banner_table where banner_type= 'imported'";
		$count_banner = $wpdb->get_results($sql1);
		$response_array['count_banner'] = $count_banner[0]->banner;
	} else if( $data_type == 'rg_pfeed_delete' )
	{
		$response_array['data_type'] = 'rg_pfeed_delete';
		$wpdb->query( "DELETE FROM $banner_table where banner_type='imported'" );	
		$sql1 = "SELECT count(*) as banner FROM $banner_table where banner_type= 'imported'";
		$count_banner = $wpdb->get_results($sql1);
		$response_array['count_banner'] = $count_banner[0]->banner;
	}
	echo json_encode($response_array);
	wp_die();
}
add_action( 'wp_ajax_revglue_store_data_delete', 'revglue_store_data_delete' );
function rg_project_type(){
	global $wpdb;
	$project_table = $wpdb->prefix.'rg_projects';
	$sql ="SELECT `expiry_date` FROM $project_table WHERE `expiry_date`='Free' ";
	$project = $wpdb->get_var($sql);
	return $project;
}
function rg_update_subscription_expiry_date($purchasekey, $userpassword, $useremail, $projectid){
	global $wpdb; 
	$projects_table = $wpdb->prefix.'rg_projects';
	$apiurl = RGPFEEDS_API_URL . "api/validate_subscription_key/$useremail/$userpassword/$purchasekey";
	$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( $apiurl , array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 
	$expiry_date = $resp_from_server['response']['result']['expiry_date'];
	if ( empty($projectid)){
		$sql ="UPDATE $projects_table SET `expiry_date` = $expiry_date WHERE `subcription_id` ='$purchasekey'";
		$wpdb->query($sql);
	} 
}
?>