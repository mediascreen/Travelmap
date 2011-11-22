<?php
/*
Plugin Name: Travelmap
Plugin URI: http://travelingswede.com/travelmap/
Description: Shows your travel plans as a map
Version: 1.4
Author: Marcus Andersson
Author URI: http://travelingswede.com
License: GPL2
*/

// Initiates the plugin
travelmap::init();

/*
TODO:
- Separate maps to separate objects
*/
class travelmap {
	
	/* Keeps track of if a shortcode has been used on the current page. Used to determine if we need to load the plugin resources. */
	static protected $shortcodeUsed;
	
	/* The public path to plugin dir */
	static protected $pluginPath; 
	
	/* The default attributes for map shortcode */
	static protected $mapDefaultAtts = array(
		'height' => '300',
		'first'  => 1,
		'last'   => false,
		'markers'=> true,
		'lines'  => true,
		'ssl'    => false
	);
	
	/* The defautl attributes for list shortcode */
	static protected $listDefaultAtts = array(
		'first'  => 1,
		'last'   => false
	);


	public function init() {
		
		self::$pluginPath = self::getPluginPath();
		
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

	static public function show_map( $atts ) {
	
		// Supported attributes with defaults
		extract( shortcode_atts( self::$mapDefaultAtts, $atts ) );
	
		// Outputs variables neded by later js-files
		$places = self::string_to_array( get_option( 'travelmap_data' ) );
	
		$places = self::filter_places( $places, $first, $last );
		if ($places === false)
			return;
	
	
		?>
		<script type="text/javascript">
		var travelmap_places = <?php echo json_encode( $places ) ?>;
		var travelmap_plugin_dir = "<?php echo self::$pluginPath ?>";
		var travelmap_markers = "<?php echo $markers ?>";
		var travelmap_lines = "<?php echo $lines ?>";
		</script>
		<?php
	
		// Limits script output only to pages where shortcode is used
		self::$shortcodeUsed = true;
	
		// Returns the html required for the map
		$map = '<div id="travelmap" style="height:' . $height . 'px; background:#FFFFFF; margin-bottom:1.5em;"></div>';
		return $map;
	}

	
	
	static public function show_list( $atts ) {
	
		extract( shortcode_atts( self::$listDefaultAtts, $atts ) );
	
		$places = self::string_to_array( get_option( 'travelmap_data' ) );
		$i = 1;
		$list = '<tr><th></th><th>Destination</th><th>Arrival</th></tr>';
	
		if ( ! is_array($places) )
			return;
	
		$places = self::filter_places( $places, $first, $last );
	
		foreach ( $places as $place ) {
	
			$printdate = ( !empty( $place['arrival'] ) ) ? date_i18n( "F j, Y", strtotime( stripslashes( $place['arrival'] ) ) ) : '-';
	
			if ( !empty( $place['url'] ) ) {
				$printplace = '<a href="' . $place['url'] . '">' . stripslashes( $place['city'] ) . ', ' . stripslashes( $place['country'] ) . '</a>';
			} else {
				$printplace = stripslashes( $place['city'] ) . ', ' . stripslashes( $place['country'] );
			}
	
			$list .= '
				<tr class="' . $place['status'] . '">
					<td>' . $i . '</td>
					<td>' . $printplace . '</td>
					<td>' . $printdate  . '</td>
				</tr>';
	
			$i++;
		}
	
		return '<table id="travelmap-list">' . $list . '</table>';
	}
	
	
	// Filter array of places to only contain entries between $first and $last from shortcode atts
	static public function filter_places( $places, $first, $last ) {
		if ( ! is_array( $places ) )
			return;
	
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
			if ( !$last )
				$last = count( $places );
	
			$filteredPlaces = array_slice( $places, $first-1, $last-($first-1) );
		}
	
		return ( count( $filteredPlaces ) > 0 ) ? $filteredPlaces : false;
	}
	
	
	// $date has to be in ISO 8601 format, ex. 2010-12-30
	static public function isValidDate( $date ) {
		$date = substr( $date, 0, 10 );
		list( $y, $m, $d ) = explode( '-', $date );
		return checkdate( (int)$m, (int)$d, (int)$y );
	}
	
	
	static public function print_js() {
	
		if ( ! self::$shortcodeUsed )
			return;
	
		wp_register_script( 'google-maps', 'http://maps.google.com/maps/api/js?sensor=false', false, false, true );
		wp_print_scripts( 'google-maps');
	
		wp_register_script( 'travelmap', plugins_url( 'travelmap.js', __FILE__ ), false, false, true );
		wp_print_scripts( 'travelmap' );
	}
	
	
	
	static public function menu() {
		add_options_page( 'Travelmap Options', 'Travelmap', 'manage_options', 'travelmap-options', array( __class__, 'options' ) );
	}
	
	
	static public function admin_init() {
	
		if ( $_GET['page'] !== 'travelmap-options') {
			return;
		}
	
		// Include javascript
		wp_enqueue_script( 'jquery-ui-sortable' );
	
		wp_register_script( 'datepicker', plugins_url( 'datepicker.js', __FILE__ ), false, false, true );
		wp_enqueue_script( 'datepicker');
	
		wp_register_script( 'google-maps', 'http://maps.google.com/maps/api/js?sensor=false', false, false, true );
		wp_enqueue_script( 'google-maps');
	
		wp_register_script( 'travelmap_admin', plugins_url( 'travelmap-admin.js', __FILE__ ), false, false, true );
		wp_enqueue_script( 'travelmap_admin' );
	
		// Register settings
		register_setting( 'travelmap_settings', 'travelmap_data', 'travelmap_textarea_to_array' );
	}
	
	
	// Main function for outputing options page
	static public function options() {
	
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
	
		$places = self::string_to_array( get_option( 'travelmap_data' ) );
		if ( ! is_array($places) ) $places = array();	
		
		include 'inc/template-admin.php';
	}
	
	
	static public function ajax_save() {
	
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
	
		// Check nonce
		if ( ! wp_verify_nonce($_POST['nonce'], 'travelmap') ) {
			header( "Status: 401 Unauthorized" );
			die( "Security check failed" );
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
	
	
	static public function string_to_array( $input ) {
		$input = trim( $input, " ;\n" );
		if ( empty($input) )
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
	
	
	static public function add_status( $places, $status = 'past' ) {
		foreach ($places as $place) {
			$i++;
			$place['status'] = $status = self::get_date_status( $places[$i]['arrival'], $status );
			$newPlaces[] = $place;
		}
		return $newPlaces;
	}
	
	// TODO: Use propper date functions
	static public function get_date_status( $date, $prevStatus = 'past') {
		if ($prevStatus == 'past') {
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
	
	
	static public function add_stylesheet() {
		wp_register_style( 'jquery-ui','http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/base/jquery-ui.css' );
		wp_register_style( 'travelmap', self::$pluginPath . 'screen.css' );
		wp_enqueue_style( 'jquery-ui' );
		wp_enqueue_style( 'travelmap' );
	}
	
	
	static public function getPluginPath() {
		return WP_PLUGIN_URL . '/' . str_replace( basename( __FILE__ ), "", plugin_basename( __FILE__ ) );
	}
}
?>