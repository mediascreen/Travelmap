function travelmap_init() {

	// Abort if we are not on plugin page
	if (adminpage != 'settings_page_travelmap-options') return;

	// Detect edit row clicks
	jQuery('#travelmap-admin-table .edit.button-secondary').live('click', function() {

		// Abort if another row is already in edit mode
		if (jQuery('#travelmap-admin-table .button-primary').length > 0) return false;

		// Find row of clicked button
		var row = jQuery(this).closest('tr');

		travelmap_edit_row(row);
		activate_row(row);
		
		return false;
	});


	// Detect new rows
	jQuery('#add-location').live('click', function() {

		// Abort if another row is already in edit mode
		if (jQuery('#travelmap-admin-table .button-primary').length > 0) return false;

		jQuery('#travelmap-admin-table').append(
			'<tr>'+
				'<td class="handle"><span class="image"></span></td>'+
				'<td class="count"></td>'+
				'<td class="city"><input type="text" /></td>'+
				'<td class="country"><input type="text" /></td>'+
				'<td class="url"><input type="text" placeholder="http://" /></td>'+
				'<td class="arrival"><input type="text" placeholder="optional" /></td>'+
				'<td class="lat"><input type="text" placeholder="optional" /></td>'+
				'<td class="lng"><input type="text" placeholder="optional" /></td>'+
				'<td class="buttons1"><a class="button-primary edit" href="#" title="Save row">Save</a></td>'+
				'<td class="buttons2"<a class="delete" href="#" title="Delete row">Delete</a></td>'+
			'</tr>'
		);

		var row = jQuery('tr:last', '#travelmap-admin-table');
		travelmap_refresh_count();
		activate_row(row);

		return false;
	});


	// Detect save clicks
	jQuery('#travelmap-admin-table .edit.button-primary').live('click', function() {

		// Find row of clicked button
		var row = jQuery(this).closest('tr');

		travelmap_exit_row_editing(row);
		return false;
	});
	// Detect enter press to save row
	jQuery('#travelmap-admin-table input').live('keypress', function(e) {
		if(e.which == 13){
			var row = jQuery(this).closest('tr');
			travelmap_exit_row_editing(row);
			return false;
		}
	});


	// Detect delete clicks
	jQuery('#travelmap-admin-table .delete').live('click', function() {

		// Find row of clicked button
		jQuery(this).closest('tr').remove();

		travelmap_save_table();
		return false;
	});


	// Reordering of rows
	jQuery("#travelmap-admin-table tbody").sortable({
		placeholder: 'ui-state-highlight',
		forcePlaceholderSize: true,
		handle: 'span.image',
		containment: 'parent',
		axis: 'y',
		tolerance: 'pointer',
		update: function(event, ui) {
			travelmap_refresh_count()
			travelmap_save_table();
		},
		helper: function(e, tr)	{
			var $originals = tr.children();
			var $helper = tr.clone();
			$helper.children().each(function(index) {
				// Set helper cell sizes to match the original sizes
				jQuery(this).width($originals.eq(index).width())
			});
			return $helper;
		},
	});


}

// Load datepicker and focus on first input
function activate_row(row) {
	jQuery('.arrival input', row).datepicker({
		dateFormat: 'yy-mm-dd', 
		duration: 'fast'}
	);
	
	jQuery('input:first', row).focus();
}

// Updates numbering of rows when reordering
function travelmap_refresh_count() {
	var i = 1;
	jQuery('#travelmap-admin-table tbody td.count').each(function(){
		jQuery(this).text(i);
		i++;
	});
}


function travelmap_edit_row(row) {
    jQuery('td', row).not('.handle, .count, .buttons1, .buttons2').each(function() {
         jQuery(this).html('<input type="text" value="' + jQuery(this).html() + '" />');
    });
	 jQuery('.edit.button-secondary', row).toggleClass('button-secondary button-primary').text('Save');

	 // TODO: Save previous info for cancel/esc
}

// TODO: Exit on esc
function travelmap_exit_row_editing(row) {
	jQuery('td', row).not('.handle, .count, .buttons1, .buttons2').each(function() {
         jQuery(this).text(
				jQuery('input', this).attr('value')
			);
	});

	var lat = jQuery(".lat", row).text();
	var lng = jQuery(".lng", row).text();

	if (!lat.length || !lng.length) {
		var address = jQuery(".city", row).text()+', '+jQuery(".country", row).text();
		travelmap_geocode(address, row);
		jQuery('.edit.button-primary', row).text('Geocoding...');
	} else {
		jQuery('.edit.button-primary', row).text('Saving...');
		travelmap_save_table();
	}
}


// Saves the entire table on every row edit.
// Probably not that much data anyway and easier than keeping track of rows serverside
// Let me know if you run in to problems with this
function travelmap_save_table() {

	var places = '';
	nonce = jQuery("#travelmap-admin-table thead").attr('class');

	jQuery("#travelmap-admin-table tbody > tr").each(function (i, tr) {
		places += jQuery("td:eq(2)", this).text()+',';
		places += jQuery("td:eq(3)", this).text()+',';
		places += jQuery("td:eq(4)", this).text()+',';
		places += jQuery("td:eq(5)", this).text()+',';
		places += jQuery("td:eq(6)", this).text()+',';
		places += jQuery("td:eq(7)", this).text()+';';
	});

	var data = {
		action: 'travelmap_ajax_save',
		places: places,
		nonce: nonce
	};

	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		dataType: 'text',
		success: function(response){
			jQuery('.edit.button-primary').toggleClass('button-secondary button-primary').text('Edit');
		}
	});

}


function travelmap_geocode(address, row) {
	var geocoder = new google.maps.Geocoder();
	geocoder.geocode( { 'address': address}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			var lat = results[0].geometry.location.lat();
			var lng = results[0].geometry.location.lng();
			jQuery("td:eq(6)", row).text(dec_round(lat, 6));
			jQuery("td:eq(7)", row).text(dec_round(lng, 6));
			jQuery('.edit.button-primary', row).text('Saving...');
			travelmap_save_table();
		} else {
			alert("Geocoding was not successful: " + status);
		}
	});
}

function dec_round(value, decimals) {
	var power = Math.pow(10, decimals || 0);
	return String(Math.round(value * power) / power);
}


travelmap_init();