<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function rg_pfeeds_import_page()
{
	?><div class="rg-admin-container">
		<h1 class="rg-admin-heading ">Import Product Feeds</h1>
		<div style="clear:both;"></div>
		<hr/>
		<div class="error_msgs "></div>
		<p class="text-right">You can filter results by RG ID, Store title</p>
		<table id="pfeeds_admin_screen_import" class="display" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>Store Logo</th>
					<th>RG ID</th>
					<th>Title</th>
					<th>Last Imported</th>
					<th>Number of Products</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Store Logo</th>
					<th>RG ID</th>
					<th>Title</th>
					<th>Last Imported</th>
					<th>Number of Products</th>
					<th>Actions</th>
				</tr>
			</tfoot>
			
		</table>
	</div><?php
}
?>