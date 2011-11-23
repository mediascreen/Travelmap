<?php
/*
Plugin Name:        Travelmap
Plugin URI:         http://travelingswede.com/travelmap/
Description:        Shows your travel plans on a Gogle map
Version:            1.5
Author:             Marcus Andersson
Author URI:         http://travelingswede.com
License:            GPL2

Copyright 2010      Marcus Andersson marcus@mediascreen.se

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2 or later
as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

travelmap::init();

class travelmap {
	
	/* The default attributes for map shortcode */
	static protected $mapDefaultAtts = array(
		'height'     => '300',        // Height of map in pixels (weight always 100% of container)
		'first'      => 1,            // The first destination to show (a number or date). A negative number is counted from the end, the last destination being -1.
		'last'       => false,        // The last destination to show (a number or a date). A negative number is counted from the end, the last destination being -1.
		'markers'    => true,         // Markers on or off
		'numbers'    => true,         // Turns marker numbering on or off
		'lines'      => true,         // Lines on or off
		'reverse'    => false,        // Reverses destination order
		'maptype'    => 'roadmap',    // Type of map: roadmap, satellite, hybrid or terrain. http://code.google.com/apis/maps/documentation/javascript/tutorial.html#MapOptions
		'ssl'        => false         // SSL on or off for external resources (not active for admin interface)
	);
	
	/* The defautl attributes for list shortcode */
	static protected $listDefaultAtts = array(
		'first'      => 1,            // The first destination to show (a number or date). A negative number is counted from the end, the last destination being -1.
		'last'       => false,        // The last destination to show (a number or date). A negative number is counted from the end, the last destination being -1.
		'reverse'    => false,        // Reverses destination order
		'dateformat' => false         // Sets custom date format. Default is the blog default format. http://codex.wordpress.org/Formatting_Date_and_Time
	);
	
	/* Used attributes - defaults overridden by specified atts */
	static protected $mapAtts;
	static protected $listAtts;
	
	/* URLs for external resources */
	static protected $googleMapsUrl = 'maps.google.com/maps/api/js?sensor=false';
	static protected $jQueryCssUrl  = 'ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/base/jquery-ui.css';
	static protected $protocol      = 'http://';
	
	/* Keeps track of if a shortcode has been used on the current page. Used to determine if we need to load the plugin resources. */
	static protected $shortcodeUsed;
	
	/* The public path to plugin dir */
	static protected $pluginPath; 


	/**
	 * Initiates the plugin by registering shortcodes and actions 
	 */
	public function init() {
		
		self::$pluginPath = self::get_plugin_path();
		
		add_shortcode( 'travelmap-map',   array( __class__, 'show_map' ) );
		add_shortcode( 'travelmap-list',  array( __class__, 'show_list' ) );
		
		add_action( 'wp_print_styles',    array( __class__, 'add_stylesheet' ) );
		add_action( 'wp_footer',          array( __class__, 'print_js' ) );
		add_action( 'admin_menu',         array( __class__, 'menu' ) );
		add_action( 'admin_init',         array( __class__, 'admin_init' ) );
		add_action( 'admin_print_styles', array( __class__, 'add_stylesheet' ) );
		
		add_action( 'wp_ajax_nopriv_travelmap_ajax_save', array( __class__, 'ajax_save' ) );
		add_action( 'wp_ajax_travelmap_ajax_save',        array( __class__, 'ajax_save' ) );
	}


	/**
	 * The travelmap-map shortcode method
	 * Prepares and outputs map data and html
	 */
	static public function show_map( $atts ) {
	
		// Set attributes
		self::$mapAtts = shortcode_atts( self::$mapDefaultAtts, self::fix_bool_atts( $atts ) );
		
		// Set protocol
		if ( self::$mapAtts['ssl'] ) {
			self::$protocol = 'https://';
		}
	
		// Outputs variables neded by later js-files
		$places = self::string_to_array( get_option( 'travelmap_data' ) );
	
		$places = self::filter_places( $places, self::$mapAtts['first'], self::$mapAtts['last'] );
		if ( false === $places )
			return;
	
		// Reverse order id reverse attr is set
		if ( true === self::$mapAtts['reverse'] ) {
			$places = array_reverse($places);
		}
	
		?>
		<script type="text/javascript">
		var travelmap_places     = <?php echo json_encode( $places ) ?>;
		var travelmap_plugin_dir = "<?php echo self::$pluginPath ?>";
		var travelmap_markers    = <?php echo self::$mapAtts['markers'] === true ? 'true' : 'false' ?>;
		var travelmap_lines      = <?php echo self::$mapAtts['lines']   === true ? 'true' : 'false' ?>;
		var travelmap_numbers    = <?php echo self::$mapAtts['numbers'] === true ? 'true' : 'false' ?>;
		var travelmap_maptype    = "<?php echo strtoupper(self::$mapAtts['maptype']) ?>";
		</script>
		<?php
	
		// Limits script output only to pages where shortcode is used
		self::$shortcodeUsed = true;
	
		// Returns the html required for the map
		return '<div id="travelmap" style="height:' . self::$mapAtts['height'] . 'px;"></div>';
	}

	
	/**
	 * The travelmap-list shortcode method
	 * Prepares and outputs list html
	 */
	static public function show_list( $atts ) {
	
		self::$listAtts = shortcode_atts( self::$listDefaultAtts, self::fix_bool_atts( $atts ) );
		
		$places = self::string_to_array( get_option( 'travelmap_data' ) );
	
		// Filter out only the destinations that are supposed to be shown
		$places = self::filter_places( $places, self::$listAtts['first'], self::$listAtts['last'] );
		
		if ( ! is_array( $places ) )
			return;
			
		// Reverse order id reverse attr is set
		if ( true === self::$mapAtts['reverse'] ) {
			$places = array_reverse( $places );
		}
		
		// Set format of dates in list
		$dateFormat = ( false !== self::$listAtts['dateformat'] ) ? self::$listAtts['dateformat'] : get_option( 'date_format' );
		$showDateCol = self::are_there_dates( $places );
		
		ob_start();
		include 'inc/template-list.php';
		return ob_get_clean();
	}
	
	
	/**
	 * Filter array of places to only contain entries between $first and $last from shortcode atts
	 * Handles dates, positive and negative values
	 */
	static protected function filter_places( $places, $first, $last ) {
		if ( ! is_array( $places ) )
			return;
	
		$placeCount = count( $places );
		$filteredPlaces = array();
		
		// If first and last is valid dates we compare dates
		if ( self::isValidDate( $first ) ) {
			if ( ! self::isValidDate( $last ) )
				$last = '2099-01-01';
				
			foreach( $places as $place ) {
				if ( $place['arrival'] >= $first && $place['arrival'] <= $last ) {
					$filteredPlaces[] = $place;
				}
			}
			
		// If first and last are not dates we assume they are integers
		} else {
			
			// Handle missing last value
			if ( ! $last ) {
				$last = $placeCount;
			}
			
			// Handle negative values
			if ( $first < 0 ) {
				$first = $placeCount + ( $first + 1 );
			}
			if ( $last < 0 ) {
				$last = $placeCount + ( $last + 1 );
			}
			
			$filteredPlaces = array_slice( $places, $first-1, $last-($first-1) );
		}
	
		return ( count( $filteredPlaces ) > 0 ) ? $filteredPlaces : false;
	}
	
	
	/** 
	 * Checks if a date is valid
	 * $date has to be in ISO 8601 format, ex. 2010-12-30
	 */
	static protected function isValidDate( $date ) {
		$date = substr( $date, 0, 10 );
		list( $y, $m, $d ) = explode( '-', $date );
		return checkdate( (int)$m, (int)$d, (int)$y );
	}
	
	
	/** 
	 * Checks if any destinations uses has dates
	 */
	static protected function are_there_dates( $places ) {
		foreach( $places as $place ) {
			if ( ! empty($place['arrival']) ) {
				return true;
			}
		}
		return false;
	}
	
	
	/** 
	 * Ads needed javascript libraries on pages where the plugin is used 
	 */
	static public function print_js() {
	
		if ( ! self::$shortcodeUsed )
			return;
	
		wp_register_script( 'google-maps', self::$protocol . self::$googleMapsUrl, false, false, true );
		wp_print_scripts( 'google-maps');
	
		wp_register_script( 'travelmap', plugins_url( 'travelmap.js', __FILE__ ), false, false, true );
		wp_print_scripts( 'travelmap' );
	}
	
	
	/** 
	 * Ads a Travelmap nav item to admin options menu
	 */
	static public function menu() {
		add_options_page( 'Travelmap Options', 'Travelmap', 'manage_options', 'travelmap-options', array( __class__, 'options' ) );
	}
	
	
	/** 
	 * Ads needed javascript libraries on the Travelmap plugin admin page 
	 */
	static public function admin_init() {
	
		if ( $_GET['page'] !== 'travelmap-options' )
			return;
	
		// Include javascript
		wp_enqueue_script( 'jquery-ui-sortable' );
	
		wp_register_script( 'datepicker', plugins_url( 'datepicker.js', __FILE__ ), false, false, true );
		wp_enqueue_script( 'datepicker');
	
		wp_register_script( 'google-maps', self::$protocol . self::$googleMapsUrl, false, false, true );
		wp_enqueue_script( 'google-maps');
	
		wp_register_script( 'travelmap_admin', plugins_url( 'travelmap-admin.js', __FILE__ ), false, false, true );
		wp_enqueue_script( 'travelmap_admin' );
	
		// Register settings
		register_setting( 'travelmap_settings', 'travelmap_data' );
	}
	
	
	/** 
	 * Prepares and outputs plugin admin options page
	 * Includes a template file for the actual html 
	 */
	static public function options() {
	
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.kk' ), __('Access denied'), array( 'response' => 401 ) );
		}
	
		$places = self::string_to_array( get_option( 'travelmap_data' ) );
		if ( ! is_array($places) ) $places = array();	
		
		include 'inc/template-admin.php';
	}
	
	
	/** 
	 * Handles the ajax posts to save destination data
	 */
	static public function ajax_save() {

		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ), __('Access denied'), array( 'response' => 401 ) );
		}
	
		// Check nonce
		if ( ! wp_verify_nonce($_POST['nonce'], 'travelmap') ) {
			wp_die( __( 'Security check failed.' ), __('Access denied'), array( 'response' => 401 ) );
		}
		
		// Save data
		if( update_option( 'travelmap_data', $_POST['places'] ) ) {
			$response = 'updated';
		} else {
			$response = 'unchanged';
		}
	
		// response output
		header( "Content-Type: text/html" );
		echo $response;
		exit;
	}
	
	
	/** 
	 * Transforms the stored string of destination info to an array
	 * TODO: Send as JSON and store as serialized array instead?
	 */
	static protected function string_to_array( $input ) {
		$input = trim( $input, " ;\n" );
		if ( empty( $input ) )
			return false;
		$rows = explode( ";", $input );
	
		foreach ( $rows as $row ) {
			$row = trim( $row, " ;," );
			list( $data['city'], $data['country'], $data['url'], $data['arrival'], $data['lat'], $data['lng'] ) = explode( ",", $row );
			$places[] = array_reverse( array_map( "trim", $data ), true );
		}
		$places = self::add_status( $places );
		return $places;
	}
	
	/** 
	 * Add information about time status to each destination to use different colors for markers
	 */
	static protected function add_status( $places, $status = 'past' ) {
		foreach ( $places as $place ) {
			$i++;
			$place['status'] = $status = self::get_date_status( $places[$i]['arrival'], $status );
			$newPlaces[] = $place;
		}
		return $newPlaces;
	}
	
	
	/** 
	 * Check if a date is in the past, present or future 
	 */
	// TODO: Use propper date functions
	static protected function get_date_status( $date, $prevStatus = 'past' ) {
		if ( $prevStatus == 'past' ) {
			if (strtotime( $date ) > time() ) {
				$status = 'present';
			} else {
				$status = 'past';
			}
		} else {
			$status = 'future';
		}
		return $status;
	}
	
	
	/** 
	 * Add stylesheets to both admin and shortcode pages
	 * TODO: Break up into separate methods for admin and public
	 */
	static public function add_stylesheet() {
		wp_register_style( 'jquery-ui', self::$protocol . self::$jQueryCssUrl );
		wp_register_style( 'travelmap', self::$pluginPath . 'screen.css' );
		wp_enqueue_style( 'jquery-ui' );
		wp_enqueue_style( 'travelmap' );
	}
	
	
	/** 
	 * Get the web path to this directory 
	 */
	static protected function get_plugin_path() {
		return WP_PLUGIN_URL . '/' . str_replace( basename( __FILE__ ), "", plugin_basename( __FILE__ ) );
	}
	
	
	/**
	 * Messy way to ensure we have actual boolean values as attributes
	 * Must be a better way to solve this
	 */
	static protected function fix_bool_atts( $atts ) {
		if ( ! is_array( $atts ) ) {
			$atts = array();
		}
		
		foreach ( $atts as $key => $value ) {
			$value = strtolower($value);
			if ( 'true' === $value ) {
				$atts[$key] = true;
			} elseif ( 'false' === $value ) {
				$atts[$key] = false;
			}
		}
		return $atts;
	}
	
}