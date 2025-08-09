<?php
/*
 * Plugin Name:       Geo User
 * Plugin URI:        https://trueans.com
 * Description:       Show customer store location
 * Version:           0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Amir Hassanzadeh
 * Author URI:        https://amirhassanzadeh.com
 * License:           
 * Text Domain:       geo user
 * Domain Path:       /languages
 */

 /*
Geo User is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Geo User is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Geo User. If not, see {URI to Plugin License}.
*/
// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * start up the engin
 */

define( 'GEO_USER_VERSION', '1.0.0' );

class WP_geo_user_plugin
{

	/**
	 * Static property to hold our singleton instance
	 *
	 */
	static $instance = false;

	function __construct()
	{
		register_activation_hook(__FILE__, [&$this, 'geo_user_activate']);
		add_action( 'admin_menu',[$this, 'plugin_menu']);


		add_action('edit_user_profile', [$this,'add_user_location_to_user_profile'],10);
		add_action('show_user_profile', [$this,'add_user_location_to_user_profile'],10);
		add_action('user_new_form', [$this,'add_user_location_to_user_profile'],10);		
		add_action('user_register', [$this,'save_user_location']);
		add_action('profile_update', [$this,'save_user_location']);
		add_action('personal_options_update', [$this,'save_user_location']);
		add_action('edit_user_profile_update', [$this,'save_user_location']);
		add_action('admin_enqueue_scripts', [$this, 'admin_enqueue']);

	}
	/**
	 * Creates a nicely formatted and more specific title element text
	 * for output in head of document, based on current view.
	 *
	 * @param string $title Default title text for current view.
	 * @param string $sep   Optional separator.
	 * @return string Filtered title.
	 */
	

	public function geo_user_activate()
	{

	}

function add_user_location_to_user_profile($user)
{
	
    if (!current_user_can('edit_user', $user->ID)) {
        return;
    }
    $long = get_user_meta($user->ID, 'long', true);
    $lat = get_user_meta($user->ID, 'lat', true);	
    ?>
    <h3><?php _e('User location'); ?></h3>
	<table class="form-table">
		<tbody>			
		<tr>
			<th>
				<label for="lat"><?php _e('Latitude') ?></label>
			</th>
			<td>
				<input type="number" id="lat" name="lat" value="<?php echo esc_attr($lat); ?>">
			</td>
		</tr>
		<tr>
				<th>
					<label for="long"><?php _e('Longitude') ?></label>
				</th>
				<td>
					<input type="number" id="long" name="long" value="<?php echo esc_attr($long); ?>">				
				</td>
			</tr>
		</tbody>
	</table>
	<div id="map" style="height:500px; width:100%;" data-lat="<?php  echo esc_attr($lat) ?>" data-long="<?php echo esc_attr($long); ?>">
	</div>    
<?php
}

#SAVE FIELDS
function save_user_location($user_id)
{
	// Check user creation only admin
	if (isset($_POST['action']) && $_POST['action'] === 'createuser') {
        if (!current_user_can('create_users')) {
            return;
        }
    }

    // Edit user
    if (isset($_POST['action']) && $_POST['action'] === 'edituser') {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }
        if (empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update-user_' . $user_id)) {
            return;
        }
    }

    

	if (!isset($_POST['action']) || $_POST['action'] === 'update') {
        if (get_current_user_id() != $user_id and !current_user_can('edit_user', $user_id)) {
            return;
        }
    }

    if (isset($_POST['lat'])) {
		$lat = floatval($_POST['lat']);
		if ($lat < -90 || $lat > 90) {
			$lat = null;
		}		
        update_user_meta($user_id, 'lat', $lat);
    }
    if (isset($_POST['long'])) {
		$long = floatval($_POST['long']);
		if ($long < -180 || $long > 180){
			$long = null;
		}
        update_user_meta($user_id, 'long', $long);
    }
}
	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function textdomain()
	{
		load_plugin_textdomain('cltextdomain', false, plugin_dir_path(__FILE__) . 'languages/');
	}
/**
 * Add plugin assets
 */
	function admin_enqueue()
	{
		wp_enqueue_style('leaflet',plugins_url('/assets/leaflet/leaflet.css', __FILE__),'1.9.3');
		wp_enqueue_script('leaflet', plugins_url('/assets/leaflet/leaflet.js', __FILE__), [], '1.9.3', false);
		wp_enqueue_script('upload-file', plugins_url('/assets/js/upload-file.js', __FILE__));
		wp_enqueue_script('add-user', plugins_url('/assets/js/add-user.js', __FILE__));
	}
	
	/**
 * Add a plugin page.
 */
public function plugin_menu() {	
	add_menu_page(
		__('Geo User1'),
		__('Geo User'),
		'manage_options',
		'geo-user',
		function(){
			wp_redirect(admin_url('admin.php?page=locations'));
            exit;
		},
		'dashicons-admin-site',

	);
	add_submenu_page(
		'geo-user',
		__('Users on the map'),
		__('Users on the map'),
		"manage_options",
		"locations", //slug
		function(){
			include trailingslashit(plugin_dir_path(__FILE__)) .  "includes" . DIRECTORY_SEPARATOR . "location.php";
		},
		3
	);
	add_submenu_page(
		'geo-user',
		__('User Data'),
		__('User Data'),
		'manage_options',
		'customer-statistics',
		function(){
			include trailingslashit(plugin_dir_path(__FILE__)) .  "includes" . DIRECTORY_SEPARATOR . "user-data.php";

		}
	);	
	add_submenu_page(
		'geo-user',
		__('settings'),
		__('Settings'),
		'manage_options',
		'geo-user-settings',
		function(){
			echo 'settins';
			exit;

		}
	);	
	add_action('admin_head', function() {
        remove_submenu_page('geo-user', 'geo-user');
    });
}

} /// END CLASS

// Instantiate our class
$WP_geo_user_plugin = WP_geo_user_plugin::getInstance();
