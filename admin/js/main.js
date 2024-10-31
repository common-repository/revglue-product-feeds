

function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;
    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};
function checkform(){
	var val = document.getElementById("feed_store").value;
	if(val == "0"){
		document.getElementById("displayMessage").innerHTML="Please select a store.";
	return false;
	}else{
	return true;
	}
				}
jQuery( document ).ready(function() {
	jQuery(".chosen-select").chosen();
	// Adds lazy load to images
	jQuery("img.revglue-unveil").unveil();
	// Initialize datepicker
	jQuery( ".pfeed_datepicker" ).datepicker();
	// Initialize Product Feeds Datatable
    jQuery('#pfeeds_admin_screen_import').DataTable({

    	"processing": true,
        "serverSide": true,
        "ajax": ajaxurl+'?action=revglue_pfeed_load_pfeeds',
		"pageLength": 50,
		"order": [[ 2, 'asc' ]],
		"drawCallback": function( settings ) {
            jQuery("#pfeeds_admin_screen_import img:visible").unveil();
        }
	});
	

	jQuery('#pfeeds_admin_screen_listing').DataTable({
    	"processing": true,
        "serverSide": true,
        "ajax": ajaxurl+'?action=revglue_pfeed_load_listing',
		"pageLength": 50,
		"order": [[ 0, 'asc' ]],
		"drawCallback": function( settings ) {
            jQuery("#pfeeds_admin_screen_listing img:visible").unveil();
        }
	});
	// Initialize Categories Datatable
    jQuery('#categories_admin_screen').DataTable({
		"bPaginate": false
	});
	// Initialize Stores Datatable
     jQuery('#stores_admin_screen').DataTable({
    	"processing": true,
        "serverSide": true,
        "ajax": ajaxurl+'?action=revglue_pfeed_load_stores',
        //"sPaginationType": "full_numbers",
		"pageLength": 50,
		"order": [[ 4, 'asc' ]],
		"drawCallback": function( settings ) {
            jQuery("#stores_admin_screen img:visible").unveil();
			jQuery('.rg_store_homepage_tag').iphoneStyle();
		}
	});
 
	// Initialize Banners Datatable
    jQuery('#banners_admin_screen').DataTable({
		"processing": true,
        "serverSide": true,
        "ajax": ajaxurl+'?action=revglue_pfeeds_load_banners',
		"pageLength": 50,
		"order": [[ 0, 'desc' ]],
		"drawCallback": function( settings ) {
            jQuery("#banners_admin_screen img:visible").unveil();
        }
	});
	jQuery( "#rg_pfeed_sub_activate" ).on( "click", function() {
		var sub_id 		= jQuery( "#rg_pfeed_sub_id" ).val();
		var sub_email 	= jQuery( "#rg_pfeed_sub_email" ).val();
		var sub_pass 	= jQuery( "#rg_pfeed_sub_password" ).val();
		if( sub_id == "" )
		{
			jQuery('#subscription_error').text("Please First enter your unique Subscription ID");	
			return false;
		}
		if( sub_email == "" )
		{
			jQuery('#subscription_error').text( "Please First enter your Email" );	
			return false;
		}
		if( sub_pass == "" )
		{
			jQuery('#subscription_error').text("Please First enter your Password");	
			return false;
		}
		var subscription_data = {
			'action'	: 'revglue_pfeeds_subscription_validate',
			'sub_id'	: sub_id,
			'sub_email'	: sub_email,
			'sub_pass'	: sub_pass
		};
		jQuery('#subscription_error').html("");
		jQuery('#subscription_response').html("");
		jQuery("#sub_loader").show();
		jQuery.post(
			ajaxurl,
			subscription_data,
			function( response )
			{		
				jQuery("#rg_pfeed_sub_id").val("");
				jQuery('#sub_loader').hide();
				jQuery('#subscription_response').html(response);
			}
		);
		return false;
	});
	jQuery( "#rg_pfeed_import" ).on( "click", function(e) {
		e.preventDefault();
		type = jQuery( this ).attr( 'href' );
		var import_data = {
			'action': 'revglue_pfeeds_data_import',
			'import_type': type
		};
		console.log("Developed By Imran Javed Twitter Handle @MrImranJaved as on 30-10-2017");
		console.log(import_data);
		
		jQuery("#subscription_error").html("");
		jQuery(".sub_page_table").hide();
		jQuery('#store_loader').show();
		jQuery.post(
			ajaxurl, 
			import_data, 
			function(response) 
			{
				console.log(response);
				jQuery('#store_loader').hide();
				jQuery(".sub_page_table").show();
				jQuery('#rg_pfeeds_import_popup').hide();
				var response_object = JSON.parse(response);
				jQuery(".sub_page_table").prepend(response_object.error_msgs);
				jQuery('#rg_pfeed_count').text(response_object.count_pfeed);	
				jQuery('#rg_category_count').text(response_object.count_category);	
				jQuery('#rg_store_count').text(response_object.count_store);	
				jQuery('#rg_pfeed_date').text(response_object.last_updated_pfeed);	
				jQuery('#rg_category_date').text(response_object.last_updated_category);
				jQuery('#rg_store_date').text(response_object.last_updated_store);
			}
		);
		return false;
	});
	jQuery( "#rg_banner_import" ).on( "click", function(e) {
		console.log("rg_banner_import");
		e.preventDefault();
		type = jQuery( this ).attr( 'href' );
		var import_data = {
			'action': 'import_revbanner_data',
			'import_type': type
		};
		console.log("Developed By Imran Javed Twitter Handle @MrImranJaved as on 30-10-2017");
		jQuery("#subscription_error").html("");
		jQuery(".sub_page_table").hide();
		jQuery('#store_loader').show();
		jQuery.post(
			ajaxurl, 
			import_data, 
			function(response) 
			{
				jQuery('#store_loader').hide();
				jQuery(".sub_page_table").show();
				jQuery('#rg_pfeeds_import_popup').hide();
				var response_object = JSON.parse(response);
				jQuery(".sub_page_table").prepend(response_object.error_msgs);
				jQuery('#rg_banner_count').text(response_object.count_banner);
			}
		);
		return false;
	});
	jQuery( "#rg_pfeed_delete" ).on( "click", function(e) {
		e.preventDefault();
		type = jQuery( this ).attr( 'href' );
		console.log(type);
		
		var delete_data = {
			'action': 'revglue_pfeeds_data_delete',
			'data_type': type
		};
		console.log("Developed By Imran Javed Twitter Handle @MrImranJaved as on 30-10-2017");
		console.log(delete_data);
		jQuery("#subscription_error").html("");
		jQuery(".sub_page_table").hide();
		jQuery('#store_loader').show();
		jQuery.post(
			ajaxurl, 
			delete_data, 
			function(response) 
			{
				console.log(response);
				jQuery('#store_loader').hide();
				jQuery(".sub_page_table").show();
				jQuery('#rg_pfeeds_delete_popup').hide();
				var response_object = JSON.parse(response);
				if( response_object.data_type == 'rg_stores' )
				{
					jQuery('#rg_store_count').text(response_object.count_store);	
					jQuery('#rg_store_date').text(response_object.last_updated_store);
				} else if( response_object.data_type == 'rg_categories' )
				{
					jQuery('#rg_category_count').text(response_object.count_category);		
					jQuery('#rg_category_date').text(response_object.last_updated_category);
				} else if( response_object.data_type == 'rg_banners' )
				{
					jQuery('#rg_banner_count').text(response_object.count_banner);
				}
			}
		);
		return false;
	});
	
	jQuery('.rg-admin-container').on('mouseenter', '.rg_store_link_pop_up', function( event ) {
		var id = this.id;
		jQuery('#imp_popup'+id).show();
	}).on('mouseleave', '.rg_store_link_pop_up', function( event ) {
		var id = this.id;
		jQuery('#imp_popup'+id).hide();
	});
	jQuery('.rg_store_homepage_tag').iphoneStyle();
	jQuery( "#stores_admin_screen" ).on( "change",  ".rg_store_homepage_tag", function(e) {
		console.log("Developed By Imran Javed Twitter Handle @MrImranJaved as on 30-10-2017");
		if( jQuery( this ).prop( 'checked' ) )
		{
		   var tag_checked = 'yes';
		} else
		{
		   var tag_checked = 'no';
		}	
		var store_tag_data = {
			'action': 'revglue_pfeeds_update_home_store',
			'store_id': this.id,
			'state' : tag_checked
		};
		jQuery.post(
			ajaxurl, 
			store_tag_data, 
			function(response) 
			{
			}
		);
	});
	jQuery('.rg_store_cat_tag_head').iphoneStyle();
	jQuery( ".rg_store_cat_tag_head" ).on( "change", function(e) {
		console.log("Developed By Imran Javed Twitter Handle @MrImranJaved as on 30-10-2017");
		if( jQuery( this ).prop( 'checked' ) )
		{
		   var tag_checked = 'yes';
		} else
		{
		   var tag_checked = 'no';
		}	
		var cat_tag_data = {
			'action': 'revglue_pfeeds_update_header_category',
			'cat_id': this.id,
			'state' : tag_checked
		};
		jQuery.post(
			ajaxurl, 
			cat_tag_data, 
			function(response) 
			{
			}
		);
	});
	jQuery('.rg_store_cat_tag').iphoneStyle();
	jQuery( ".rg_store_cat_tag" ).on( "change", function(e) {
		console.log("Developed By Imran Javed Twitter Handle @MrImranJaved as on 30-10-2017");
		if( jQuery( this ).prop( 'checked' ) )
		{
		   var tag_checked = 'yes';
		} else
		{
		   var tag_checked = 'no';
		}	
		var cat_tag_data = {
			'action': 'revglue_pfeeds_update_popular_category',
			'cat_id': this.id,
			'state' : tag_checked
		};
		jQuery.post(
			ajaxurl, 
			cat_tag_data, 
			function(response) 
			{
			}
		);
	});
	jQuery( ".rg_pfeeds_open_import_popup" ).on( "click", function(e) {
		e.preventDefault();
		var type = jQuery( this ).attr( "href" );
		jQuery('#rg_pfeeds_delete_popup').hide();	
		jQuery('#rg_pfeeds_import_popup').show();
		jQuery('.rg_pfeeds_start_import').attr( "href", type );
	});



	jQuery( ".rg_pfeeds_open_delete_popup" ).on( "click", function(e) {
		e.preventDefault();
		var type = jQuery( this ).attr( "href" );
		jQuery('#rg_pfeeds_import_popup').hide();
		jQuery('#rg_pfeeds_delete_popup').show();	
		jQuery('.rg_pfeeds_start_delete').attr( "href", type );
	});



	jQuery('#rg_banner_image_type').on( "change", function(e) {
		console.log("Developed By Imran Javed Twitter Handle @MrImranJaved as on 30-10-2017");
		var type = jQuery( this ).val();
		if( type == 'url' )
		{
			jQuery('#rg_banner_image_file').val('');
			jQuery('#rg_pfeeds_banner_image_upload').hide();
			jQuery('#rg_pfeeds_banner_image_url').show();
		} else
		{
			jQuery('#rg_banner_image_url').val('');
			jQuery('#rg_pfeeds_banner_image_url').hide();
			jQuery('#rg_pfeeds_banner_image_upload').show();
		}
	});
	// Set all variables to be used in scope
	var frame;
	// ADD ICON LINK
	jQuery( "#categories_admin_screen" ).on( "click", ".rg_add_category_icon", function( event ) {
		console.log("Developed By Imran Javed Twitter Handle @MrImranJaved as on 30-10-2017");
		var the_cat_id = this.id
		event.preventDefault();
		/* // If the media frame already exists, reopen it.
		if ( frame ) 
		{
			frame.open();
			return;
		} */
		// Create a new media frame
		frame = wp.media({
			title: 'Select or Upload Media Of Your Chosen Persuasion',
			button: 
			{
				text: 'Use this media'
			},
			multiple: false  // Set to true to allow multiple files to be selected
		});
		// When an image is selected in the media frame...
		frame.on( 'select', function() 
		{
			// Get media attachment details from the frame state
			var attachment = frame.state().get('selection').first().toJSON();
			var cat_img_data = {
				'action': 'revglue_pfeeds_update_category_icon',
				'cat_id': the_cat_id,
				'icon_url' : attachment.url
			};
			jQuery.post(
				ajaxurl, 
				cat_img_data, 
				function(response) 
				{
					jQuery( ".rg_store_icon_thumb_"+response ).html( 
					"<a id='"+response+"' class='rg_category_delete_icons' href='javascript;'>"+
					"<i class='fa fa-times' aria-hidden='true'></i></a>"+
					"<img alt='image' src='"+attachment.url+"'>" );
					jQuery( ".rg_add_category_icon_"+response ).text('Edit Icon');
				}
			);
		});
		// Finally, open the modal on click
		frame.open();
	});
	// DELETE ICON LINK
	jQuery( "#categories_admin_screen" ).on( "click",  ".rg_category_delete_icons", function( event ) {
		console.log("Developed By Imran Javed Twitter Handle @MrImranJaved as on 30-10-2017");
		var the_cat_id = this.id
		event.preventDefault();
		jQuery.confirm({
			title: 'Category Icon',
			content: 'Are you sure you want to remove this icon ?',
			icon: 'fa fa-question-circle',
			animation: 'scale',
			closeAnimation: 'scale',
			opacity: 0.5,
			buttons: {
				'confirm': {
					text: 'Remove',
					btnClass: 'btn-blue',
					action: function () {
						var cat_img_data = {
							'action': 'revglue_pfeeds_delete_category_icon',
							'cat_id': the_cat_id,
						};
						jQuery.post(
							ajaxurl, 
							cat_img_data, 
							function(response) 
							{
								console.log(response);
								jQuery( ".rg_store_icon_thumb_"+response ).html( '' );
								jQuery( ".rg_add_category_icon_"+response ).text('Add Icon');
							}
						);
					}
				},
				cancel: function () {
				},
			}
		});	
	});
	// ADD IMAGE LINK
	jQuery( "#categories_admin_screen" ).on( "click",  ".rg_add_category_image", function( event ) {
		console.log("Developed By Imran Javed Twitter Handle @MrImranJaved as on 30-10-2017");
		var the_cat_id = this.id
		event.preventDefault();
		/* // If the media frame already exists, reopen it.
		if ( frame ) 
		{
			frame.open();
			return;
		} */
		// Create a new media frame
		frame = wp.media({
			title: 'Select or Upload Media Of Your Chosen Persuasion',
			button: 
			{
				text: 'Use this media'
			},
			multiple: false  // Set to true to allow multiple files to be selected
		});
		// When an image is selected in the media frame...
		frame.on( 'select', function() 
		{
			// Get media attachment details from the frame state
			var attachment = frame.state().get('selection').first().toJSON();
			var cat_img_data = {
				'action': 'revglue_pfeeds_update_category_image',
				'cat_id': the_cat_id,
				'image_url' : attachment.url
			};
			jQuery.post(
				ajaxurl, 
				cat_img_data, 
				function(response) 
				{
					jQuery( ".rg_store_image_thumb_"+response ).html( 
					"<a id='"+response+"' class='rg_category_delete_icons' href='javascript;'>"+
					"<i class='fa fa-times' aria-hidden='true'></i></a>"+
					"<img alt='image' src='"+attachment.url+"'>" );
					jQuery( ".rg_add_category_image_"+response ).text('Edit Image');
				}
			);
		});
		// Finally, open the modal on click
		frame.open();
	});
	// DELETE IMAGE LINK
	jQuery( "#categories_admin_screen" ).on( "click",  ".rg_category_delete_images", function( event ) {
		console.log("Developed By Imran Javed Twitter Handle @MrImranJaved as on 30-10-2017");
		var the_cat_id = this.id
		event.preventDefault();
		jQuery.confirm({
			title: 'Category Image',
			content: 'Are you sure you want to remove this image ?',
			icon: 'fa fa-question-circle',
			animation: 'scale',
			closeAnimation: 'scale',
			opacity: 0.5,
			buttons: {
				'confirm': {
					text: 'Remove',
					btnClass: 'btn-blue',
					action: function () {
						var cat_img_data = {
							'action': 'revglue_pfeeds_delete_category_image',
							'cat_id': the_cat_id,
						};
						jQuery.post(
							ajaxurl, 
							cat_img_data, 
							function(response) 
							{
								console.log(response);
								jQuery( ".rg_store_image_thumb_"+response ).html( '' );
								jQuery( ".rg_add_category_image_"+response ).text('Add Image');
							}
						);
					}
				},
				cancel: function () {
				},
			}
		});	
	});
	jQuery( "#pfeeds_admin_screen_import" ).on( "click", ".rg_import_pfeed", function( event ) {
		console.log("Developed By Imran Javed Twitter Handle @MrImranJaved as on 30-10-2017");
		var rg_store_id = jQuery(this).data('rg_store_id');
		console.log(rg_store_id + " Store id");
		jQuery("#pfeed_antiloader_"+rg_store_id).hide();
		jQuery("#pfeed_loader_"+rg_store_id).show();
		var pfeed_data = {
			'action': 'revglue_pfeeds_get_product_feeds',
			'rg_store_id': rg_store_id
		};
		console.log(pfeed_data);
		jQuery.post(
			ajaxurl, 
			pfeed_data, 
			function(response) 
			{
				 console.log(response);
				var response_object = JSON.parse(response);
				jQuery("#pfeed_loader_"+response_object.rg_store_id).hide();
				jQuery('.error_msgs').prepend(response_object.error_msgs)
				jQuery("#pfeed_updated_"+response_object.rg_store_id).text(response_object.last_updated_feed);
				jQuery("#pfeed_fcount_"+response_object.rg_store_id).text(response_object.count_feed);
				jQuery( "#pfeed_antiloader_"+response_object.rg_store_id ).html( "<a href='javascript:' class='rg_import_pfeed btn btn-primary txtwhite' data-rg_store_id='"+response_object.rg_store_id+"' >Import</a>" );
				jQuery("#pfeed_antiloader_"+response_object.rg_store_id).show();
			}
		);
	});


	jQuery( "#rg_banner_delete" ).on( "click", function(e) {
		e.preventDefault();
		console.log("rg_banner_delete");
		 console.log("Developed By Imran Javed Twitter Handle @MrImranJaved as on 30-10-2017");
		
		type = jQuery( this ).attr( 'href' );
		console.log(type);
		var delete_data = {
			'action': 'revglue_store_data_delete',
			'data_type': type
		};
		console.log(delete_data);
		 

		jQuery("#subscription_error").html("");
		jQuery(".sub_page_table").hide();
		jQuery('#store_loader').show();
		jQuery.post(
			ajaxurl, 
			delete_data, 
			function(response) 
			{
				console.log(response);
				jQuery('#store_loader').hide();
				jQuery(".sub_page_table").show();
				jQuery('#rg_pfeeds_delete_popup').hide();
				var response_object = JSON.parse(response);
				if( response_object.data_type == 'rg_banners' )
				{
					jQuery('#rg_banner_count').text(response_object.count_banner);
				}
			}
		);
		return false;
	}); 
 
});