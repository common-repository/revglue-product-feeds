<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function rg_pfeeds_listing_page()
{
	global $wpdb;
	$stores_table = $wpdb->prefix.'rg_stores';
	$pfeeds_table = $wpdb->prefix.'rg_pfeeds';
	$categories_table = $wpdb->prefix.'rg_categories';
	
	if( isset($_REQUEST['feed_id']) )
	{
		if( isset( $_REQUEST['action']) && $_REQUEST['action'] == 'approve' )
		{
			$wpdb->update( 
				$pfeeds_table, 
				array( 
					'status' => 'active'
				), 
				array( 'rg_id' => absint( $_REQUEST['feed_id'] ) )
			);				
		} else if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'disapprove' )
		{
			$wpdb->update( 
				$pfeeds_table, 
				array( 
					'status' => 'inactive'
				), 
				array( 'rg_id' => absint( $_REQUEST['feed_id'] ) )
			);		
		}
	}
	
	
	?><div class="rg-admin-container">
		<h1 class="rg-admin-heading ">Product Feeds</h1>
		<div style="clear:both;"></div>
		<hr/>
		
		<?php if( $_SERVER['REQUEST_METHOD'] == 'POST')
		{
			 
			

			if( !empty( $_POST['feed_keyword'] ) )
			{
				$sanitized_search_string = sanitize_text_field( $_POST['feed_keyword'] );
				$key_param = " AND title LIKE '%$sanitized_search_string%'";
			} else 
			{
				$key_param = "";
			}
			
			if( !empty( $_POST['feed_store'] ) )
			{
				$sanitized_store = absint( $_POST['feed_store'] );
				$store_param = " AND rg_store_id = $sanitized_store";
			} else 
			{
				$store_param = "";
			}
			
			if( !empty( $_POST['feed_min'] ) )
			{
				$sanitized_min = absint( $_POST['feed_min'] );
				$min_param = " AND price >= $sanitized_min";
			} else 
			{
				$min_param = "";
			}
			
			if( !empty( $_POST['feed_max'] ) )
			{
				$sanitized_max = absint( $_POST['feed_max'] );
				$max_param = " AND price <= $sanitized_max";
			} else 
			{
				$max_param = "";
			}
			
			if( !empty( $_POST['feed_brand'] ) )
			{
				$sanitized_brands = sanitize_text_field( $_POST['feed_brand'] );
				$brand_param = " AND brand LIKE '%$sanitized_brands%'";
			} else 
			{
				$brand_param = "";
			}
			
			$store_id = sanitize_text_field($_POST['store_id']) ;
			$sql  = "SELECT *FROM $pfeeds_table WHERE 1  $key_param$min_param$max_param$brand_param$store_param"; 
			$feeds = $wpdb->get_results($sql);
			}
			?>
			<div class="text-right">You can filter the results by title.</div>
			<table id="pfeeds_admin_screen_listing" class="display" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th>Title</th>
						<th>Product Image</th>
						<th>Deeplink</th> 
						<th>Price</th>
						<th>RRP</th>
						<th>Category</th>
						<th>Status</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th>Title</th>
						<th>Product Image</th>
						<th>Deeplink</th> 
						<th>Price</th>
						<th>RRP</th>
						<th>Category</th>
						<th>Status</th>
					</tr>
				</tfoot>

			</table>
	 </div><?php
}
?>