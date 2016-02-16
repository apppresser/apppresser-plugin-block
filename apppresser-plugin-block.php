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

		// is main plugin active? If not, throw a notice and deactivate
		if ( ! in_array( 'apppresser/apppresser.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			add_action( 'all_admin_notices', array( $this, 'apppresser_required' ) );
			return;
		}

		add_filter( 'option_active_plugins', array( $this, 'appp_filter_plugins' ), 5 );
		
	}
	
	
	// remove some plugins
	function appp_filter_plugins( $active = array() ) {
		
		if( isset( $_GET['appp'] ) && ( $_GET['appp'] == 1 || $_GET['appp'] == 2 ) &&  || 
			isset( $_COOKIE['AppPresser_Appp'] ) && $_COOKIE['AppPresser_Appp'] === 'true' ||
			isset( $_COOKIE['AppPresser_Appp2'] ) && $_COOKIE['AppPresser_Appp2'] === 'true' ) {
	
			$exclude = apply_filters( 'appp_exclude_plugins', $active );
	
			foreach ( $exclude as $plugin ) {
				$key = array_search( $plugin, $active );
				if ( false !== $key ) {
					unset( $active[ $key ] );
				}
			}
		}
		
		return $active;
	}


	/**
	* apppresser_required function.
	*
	* @access public
	* @return void
	*/
	public function apppresser_required() {
	echo '<div id="message" class="error"><p>'. sprintf( __( '%1$s requires the AppPresser Core plugin to be installed/activated. %1$s has been deactivated.', 'apppresser' ), self::PLUGIN ) .'</p></div>';
	deactivate_plugins( self::$this_plugin, true );
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
}
add_action( 'admin_init', 'apb_admin_init' );


function section_one_callback() {
    echo 'Checked plugins will be deactived on mobile.';
}

function field_one_callback() {
    $setting = maybe_unserialize( get_option( 'apppresser-plugin-block' ) );
    
    //var_dump($settings);
            
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
    	'appwoo/apppresser-woocommerce.php' => ''
    );
    
    $plugins = array_diff_key($plugins, $keep);        
    $array_keys = array_keys( $plugins );
    
	foreach( $array_keys as $key ){	
		
		if( !empty($setting) )
		$checked = ( in_array( $key, $setting ) ) ? $checked = 'checked="checked"' : $checked = '' ;

		echo "<input type='checkbox' $checked name='apppresser-plugin-block[]' value='$key' />" . $plugins[$key]['Title'] . '</br>';
	}
        
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