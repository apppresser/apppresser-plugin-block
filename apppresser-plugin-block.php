<?php
/*
Plugin Name: AppPresser Plugin Block
Description: Blocks plugins from loading in AppPreser app.
Author: AppPresser Team
Version: 1.0
Author URI: http://apppresser.com
*/

class AppPresserPluginBlock {

	// A single instance of this class.
	public static $instance    = null;
	public static $this_plugin = null;
	const PLUGIN               = 'AppPresser Plugin Block';
	const VERSION              = '1.0';
	public static $dir_path;
	public static $dir_url;
	public static $is_apppv;

	/**
	* run function.
	*
	* @access public
	* @static
	* @return void
	*/
	public static function run() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	* __construct function.
	*
	* @access public
	*/
	public function __construct() {
	
		self::$dir_path = trailingslashit( plugin_dir_path( __FILE__ ) );
		self::$dir_url = trailingslashit( plugins_url( null , __FILE__ ) );

		add_filter( 'option_active_plugins', array( $this, 'appp_filter_plugins' ), 5 );
		
	}
	
	
	// remove some plugins
	function appp_filter_plugins( $active = array() ) {

		if( is_admin() ) {
			return $active;
		}

		// handle API plugin blocking
		if( get_option( 'apppresser-plugin-block-api' ) === '1' && $_SERVER['REQUEST_URI'] && strpos( $_SERVER['REQUEST_URI'], 'wp-json' ) != false ) {
			$exclude = apply_filters( 'appp_exclude_plugins', $active );

			$exclude = maybe_unserialize( $exclude );
	
			foreach ( $exclude as $plugin ) {
				$key = array_search( $plugin, $active );
				if ( false !== $key ) {
					unset( $active[ $key ] );
				}
			}

			return $active;
		}

		if( self::read_app_version() ) {

			$exclude = apply_filters( 'appp_exclude_plugins', $active );

			$exclude = maybe_unserialize( $exclude );
	
			foreach ( $exclude as $plugin ) {
				$key = array_search( $plugin, $active );
				if ( false !== $key ) {
					unset( $active[ $key ] );
				}
			}
		}
		
		return $active;
	}

	public static function read_app_version() {
		if ( self::$is_apppv !== null )
			return self::$is_apppv;

		if( isset( $_GET['appp'] ) && $_GET['appp'] == 3 || isset( $_COOKIE['AppPresser_Appp3'] ) && $_COOKIE['AppPresser_Appp3'] === 'true' ) {
			self::$is_apppv = 3;
		} else if( isset( $_GET['appp'] ) && $_GET['appp'] == 2 || isset( $_COOKIE['AppPresser_Appp2'] ) && $_COOKIE['AppPresser_Appp2'] === 'true' ) {
			self::$is_apppv = 2;
		} else if( ( isset( $_GET['appp'] ) && $_GET['appp'] == 1 ) || isset( $_COOKIE['AppPresser_Appp'] ) && $_COOKIE['AppPresser_Appp'] === 'true' ) {
			self::$is_apppv = 1;
		} else {
			self::$is_apppv = 0;
		}

		return self::$is_apppv;
	}

}
AppPresserPluginBlock::run();


function appp_filter_exclude_plugins( $exclude = array() ) {
	/* CONFIGURE PLUGINS TO REMOVE HERE!
	--------------------------------------------------------------------------------------------- */
	// Add the name of the main plugin php file that you want to exclude here, to the array, and return.
	// Below are some example ones. Feel free to delete these and add your own.
		
	$exclude = ( maybe_unserialize( get_option( 'apppresser-plugin-block' ) ) ) ? maybe_unserialize( get_option( 'apppresser-plugin-block' ) ) : array();

	return $exclude;
}
add_filter( 'appp_exclude_plugins', 'appp_filter_exclude_plugins' );




function apb_admin_menu() {
    add_options_page( 'Plugin Block', 'Plugin Block', 'manage_options', 'apppresser-plugin-block', 'apb_options_page' );
}
add_action( 'admin_menu', 'apb_admin_menu' );


function apb_admin_init() {
    register_setting( 'apppresser-plugin-block-group', 'apppresser-plugin-block', 'apb_validate_input' );
    add_settings_section( 'section-one', 'Choose Plugins', 'section_one_callback', 'apppresser-plugin-block' );
    add_settings_field( 'field-one', 'Plugins', 'field_one_callback', 'apppresser-plugin-block', 'section-one' );

    register_setting( 'apppresser-plugin-block-group', 'apppresser-plugin-block-api', 'apb_validate_input' );
    add_settings_field( 'field-two', 'Deactivate for WP-API Calls', 'field_two_callback', 'apppresser-plugin-block', 'section-one' );
}
add_action( 'admin_init', 'apb_admin_init' );


function section_one_callback() {
    echo 'Checked plugins will be deactived on mobile.';
}

function field_two_callback() {
	$setting = maybe_unserialize( get_option( 'apppresser-plugin-block-api' ) );

	echo '<p><input type="checkbox" ' . ( $setting ? "checked" : "" ) . ' name="apppresser-plugin-block-api" value="1" />Deactive for WP-API (check if you have issues with API based integrations like AppCommunity or AppCommerce)</p>';
}

function field_one_callback() {
    $setting = maybe_unserialize( get_option( 'apppresser-plugin-block' ) );

    // Crazy, WordPress will serialize it twice, WHAT!
    $setting = maybe_unserialize( maybe_unserialize( $setting ) );

    $active_plugins = get_option('active_plugins');

    $apl=get_option('active_plugins');
    $plugins=get_plugins();
    $activated_plugins=array();
    foreach ($apl as $p){           
        if(isset($plugins[$p])){
             array_push($activated_plugins, $plugins[$p]);
        }           
    }
            
    $plugins = get_plugins();
    
    $keep = array(
    	'apppresser-plugin-block/apppresser-plugin-block.php' => '',
    	'apppresser/apppresser.php' => '',
    	'appgeo/apppresser-geolocation.php' => '',
    	'appbuddy/appbuddy.php' => '',
    	'appcamera/appp-camera.php' => '',
    	'apppush/appp-push.php' => '',
    	'appshare/appshare.php' => '',
    	'appswiper/apppresser-swipers.php' => '',
    	'appwoo/apppresser-woocommerce.php' => '',
    	'appcommerce/appcommerce.php' => '',
    	'appcommunity/appcommunity.php' => '',
    	'applms/applms.php' => ''
    );
    
    $plugins = array_diff_key($plugins, $keep);        
    $array_keys = array_keys( $plugins );

    $active_html = '';
    $deactive_html = '';
    
	foreach( $array_keys as $key ) {
		
		$checked = ( !empty($setting) && in_array( $key, $setting ) ) ? 'checked="checked"' : '' ;

		if( in_array( $key, $active_plugins ) ) {
			$status = '<span class="wp-ui-text-highlight">active</span>';
			$before = '<strong>';
			$after  = '</strong>';
			$css  = 'active';
		} else {
			$status = '<span class="wp-ui-text-notification">deactive</span>';
			$before = '';
			$after  = '';
			$css  = 'deactive';
		}
		
		 $html = "<p><input class=\"$css\" type='checkbox' $checked name='apppresser-plugin-block[]' value='$key' />" . $before . $plugins[$key]['Title'] . $after . ' - ' . $status . '</p>';

		 if( $before ) {
		 	$active_html .= $html;
		 } else {
		 	$deactive_html .= $html;
		 }
	}

	echo '<div class="disable-plugins">';
    echo '<p><a href="#" onclick="selectAllPlugins()">select all</a> | <a href="#" onclick="selectNonePlugins()">select none</a></p>';
    echo $active_html;
    // echo $deactive_html;
    echo '</div>';
    echo '<script type="text/javascript">
    	function selectAllPlugins() {
    		jQuery(\'input[type="checkbox"]\').prop( "checked", true );
    		return false;
    	}
    	function selectNonePlugins() {
    		jQuery(\'input[type="checkbox"]\').prop( "checked", false );
    		return false;
    	}
    	</script>
    ';
}

function apb_validate_input( $input ) {
	return maybe_serialize($input);
}



function apb_options_page() {
    ?>
    <div class="wrap">
        <h2>AppPresser Plugin Block</h2>
        <form action="options.php" method="POST">
            <?php settings_fields( 'apppresser-plugin-block-group' ); ?>
            <?php do_settings_sections( 'apppresser-plugin-block' ); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
