<?php
if ( !defined('WP_UNINSTALL_PLUGIN') ) {
	exit();
}

// Deletes travelmap options
delete_option('travelmap_data');
?>