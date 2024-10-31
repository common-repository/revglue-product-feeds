<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
function rg_pfeeds_main_page()
{

	$re='';
	  $check = rg_check_subscription();
	  $re=  $check=="Free"?"RevEmbed" :"RevGlue" ;

	?><div class="rg-admin-container">
		<h1 class="rg-admin-heading ">Welcome to <?php echo $re;?> Product Feeds WordPress Plugin</h1>
		<div style="clear:both;"></div>
		<hr/>
		<div class="panel-white mgBot">
			<h3>Introduction</h3>
			<p>RevGlue provides WordPress plugins for affiliates that are free to download and earn 100% commissions. RevGlue provides the following WordPress plugins.</p>
			<ul class="pagelist">
				<li>RevGlue Product Feed  - setup your shopping directory</li>
				<li>RevGlue Vouchers – setup your vouchers / coupons website.</li>
				<li>RevGlue Cashback – setup your cashback website within minutes.</li>
				<li>RevGlue Daily Deals – setup your daily deals aggregation engine in minutes.</li>
				<li>RevGlue Mobile Comparison – setup mobile comparison website in minutes.</li>
				<li>Banners API – add banners on your projects integrated in all plugins above.</li>
				<li>Broadband & TV -  setup broadband, tv and phone comparison website in minutes.</li>
			</ul>  
		</div>
		<div class="panel-white mgBot">

		<?php
			$check = rg_check_subscription();
			if($check =="Free"){

		?>
			<h3><?php echo $re;?> Product Feed Data and WordPress CMS Plugin</h3>
			<p>There are two ways you can obtain Product Feed data in this plugin.</p>
			<p> <b> 1 </b> - Subscribe to RevGlue affiliate Product Feed data for £60 and add your own affiliate network IDs to earn 100% commission on your affiliate network accounts.      
			Try is free for the first 30 days. Create RevGlue.com user account and subscribe with affiliate Product Feed data set today. </p>
			<p> <b>2 </b> - You can use RevEmbed Product Feed data set that is free to use and you are not required to create affiliate network accounts. RevEmbed data set for Product Feed offers 80% commission to you on all the sales referred from your Product Feed website. This is based on revenue share basis with RevGlue that saves your time and money and provides you ability to create your Product Feed website in minutes. Browse RevEmbed module. Once you register for any both data source from the options given above. 
			You will be provided with the project unique id that you are required to add in Import Product Feed section and fetch the Product Feed data. </p>
      
      <?php }else{?>
			
		<h3>RevGluess Product Feed WordPress CMS Plugin</h3>
			<p>The aim of RevGlue Product Feed plugin is to allow you to setup a shopping directory in UK. You will earn 100% commissions generated via the plugin and the CMS is totally free for all affiliates. You may make further copies or download latest versions from RevGlue website. You will require RevGlue account and then subscribe to RevGlue Product Feeds data set for any country you wish to setup the shopping directory. </p>
		
	 <?php } ?>


		</div>
		<div class="panel-white mgBot">
			<h3><?php echo $re;?> Product Feed Menu Explained</h3>
			<p><b>Import Stores</b> - Add your RevGlue Data account credentials to validate your account and obtain RevGlue Product Feed Stores Data. </p>
			<p><b>Stores</b> - Shows all stores data obtained via RevGlue Data API. The Data api only fetches the stores you have selected on your RevGlue account so make sure you have selected all the Product Feed stores. </p>
			<p><b>Product Categories</b>- Product Feed categories obtained from RevGlue Product Feed Data API under upload Product Feed menu.</p>
			<p><b>Import Product Feeds</b>– Add your RevGlue Data account credentials to validate your account and obtain RevGlue Product Feed Data. </p>
			<p><b>Product Feeds</b>– Shows all Product Feed data obtained via RevGlue Data API. </p>
			<p><b>Product Reviews</b> -These are user reviews on this local WordPress and does not relate to any api data. You can validate each review before setting it live. </p>
			<p><b>Import Banners</b>– Add your RevGlue Data account credentials to validate your account and obtain RevGlue Banners Data. Use CRON file path to setup on your server to auto update the data dynamically.</p>
			<p><b>Banners</b>- Allows you to add your own banner on website placements that are pre-defined for you. You may add multiple banners on one placements and they will auto change on each refresh. You may also subscribe with RevGlue Banners API and obtain latest banners for each Product Feed from RevGlue Banners. The banners you may add are known as LOCAL banners and others obtained via RevGlue Banner API are shown as RevGlue Banners.</p>
			<p><b>Exit Clicks</b> - This report shows all exit clicks from your WordPress project.</p>
			<?php
      $check = rg_check_subscription();
      if($check =="Free"){?>

		<p><b>Commissions</b> - This report shows all the commissions that are paid, pending or confirmed.</p>
		<p><b>Payments</b> - This report shows payments and show all the paid commissions ,  click on payment number button.</p>

	  <?php }?>
			<p><b>Newsletter Subscribers</b>- Here is the list of all newsletter subscribers for you that have opted in for newsletter on your WordPress stores cms. </p>
		</div>
		<div class="panel-white mgBot">
			<h3>Further Development</h3>
			<p>If you wish to add new modules or require additional design or development changes then contact us on <a href="#">support@revglue.com</a></p>
			<p>
				We are happy to analyse the required work and provide you a quote and schedule. 
			</p> 
		</div>
		<div class="panel-white mgBot">
			<h3>Useful Links</h3>
			<p><b>RevGlue</b>- <a href="https://www.revglue.com/" target="_blank">https://www.revglue.com/</a></p>
			<p><b>RevGlue Product Feeds Data</b>- <a href="https://www.revglue.com/data" target="_blank">https://www.revglue.com/data</a></p>
			<p><b>RevGlue WordPress Plugins</b>- <a href="https://www.revglue.com/free-wordpress-plugins" target="_blank"> https://www.revglue.com/free-wordpress-plugins</a></p>
			<p><b>RevGlue WordPress Templates</b>- <a href="https://www.revglue.com/affiliate-website-templates" target="_blank">https://www.revglue.com/affiliate-website-templates</a></p>
		</div>
	</div><?php		
}
?>