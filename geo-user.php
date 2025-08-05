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
		add_action('init',[$this,'excel_file_post_type']);
		add_action('add_meta_boxes', [$this,'CSV_file_add_metabox']);
		add_action('save_post', [$this,'csv_file_save'], 10, 1);


		add_action('user_register', [$this,'save_user_location']);
		add_action('profile_update', [$this,'save_user_location']);
		add_action('personal_options_update', [$this,'save_user_location']);
		add_action('edit_user_profile_update', [$this,'save_user_location']);

		add_action('admin_enqueue_scripts', [$this, 'admin_enqueue']);

		add_action( 'wp_ajax_my_action', [$this,'my_action']);

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
				<input type="number" id="lat" name="lat" value="<?php echo $lat ?>">
			</td>
		</tr>
		<tr>
				<th>
					<label for="long"><?php _e('Longitude') ?></label>
				</th>
				<td>
					<input type="number" id="long" name="long" value="<?php echo $long ?>">				
				</td>
			</tr>
		</tbody>
	</table>
	<div id="map" style="height:500px; width:100%;" data-lat="<?php echo $lat ?>" data-long="<?php echo $long; ?>">

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

    if (isset($_POST['lat']) && is_numeric($_POST['lat'])) {
        update_user_meta($user_id, 'lat', sanitize_text_field($_POST['lat']));
    }
    if (isset($_POST['long']) && is_numeric($_POST['long'])) {
        update_user_meta($user_id, 'long', sanitize_text_field($_POST['long']));
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
	add_submenu_page(
		'users.php',
		__('Users on the map'),
		__('Users on the map'),
		"manage_options",
		"customer-location", //slug
		function(){
			include trailingslashit(plugin_dir_path(__FILE__)) .  "includes" . DIRECTORY_SEPARATOR . "location.php";
		},
		3
	);
	add_submenu_page(
		'',
		__('User Data'),
		__('User Data'),
		'manage_options',
		'customer-statistics',
		function(){
			include trailingslashit(plugin_dir_path(__FILE__)) .  "includes" . DIRECTORY_SEPARATOR . "user-data.php";

		}
	);
}
/**
 * create new post type for save excel file and read after
 */
public static function excel_file_post_type() {
	register_post_type('cl-csv',
		array(
			'labels'      => array(
				'name'          => __('csv', 'textdomain'),
				'singular_name' => __('csv', 'textdomain'),
			),
				'public'      => true,
				'has_archive' => false,
				'exclude_from_search' => true,
				'show_in_menu' => 'users.php',
				'show_in_nav_menus' => true,
				'show_in_rest' => false,
				'menu_position'=>4,
				'supports' => [
					'title',
					'editor',
					'revisions',
					'author',
				],
				'rewrite' => false,
		)
	);
}

function CSV_file_add_metabox()
{
    add_meta_box('csvfilediv', __('CSV file', 'text-domain'), [$this,'CSV_file_metabox'], 'cl-csv');
}

function CSV_file_metabox($post)
{
    global $content_width, $_wp_additional_image_sizes;

    $csv_id = get_post_meta($post->ID, '_csv_file', true);

    $old_content_width = $content_width;
    $content_width = 254;

    if ($csv_id && get_post($csv_id)) {        
		$csv_url = wp_get_attachment_url($csv_id);
        if (!empty($csv_url)) {
            $content = '<a href="'.$csv_url . '">فایل جاری</a>';
            $content .= '<p class="hide-if-no-js"><a href="javascript:;" id="remove_csv_file_button" >' . esc_html__('Remove Mobile Image', 'text-domain') . '</a></p>';
            $content .= '<input type="hidden" id="upload_listing_image" name="_listing_cover_image" value="' . esc_attr($csv_id) . '" />';
        }
        $content_width = $old_content_width;
    } else {
		$content ='<a href="" style="display:none;">فایل جاری</a>';
        $content .= '<p class="hide-if-no-js"><a title="' . esc_attr__('select csv file', 'text-domain') . '" href="javascript:;" id="upload_csv_file_button" id="set-csv-file" data-uploader_title="' . esc_attr__('select a csv file', 'text-domain') . '" data-uploader_button_text="' . esc_attr__('set csv file', 'text-domain') . '">' . esc_html__('set csv file', 'text-domain') . '</a></p>';
        $content .= '<input type="hidden" id="upload_csv_file" name="_csv_file" value="" />';
    }

    echo $content;
}

function csv_file_save($post_id)
{
    if (isset($_POST['_csv_file'])) {
        $csv_id = (int) $_POST['_csv_file'];
        update_post_meta($post_id, '_csv_file', $csv_id);
    }
}


} /// END CLASS

// Instantiate our class
$WP_geo_user_plugin = WP_geo_user_plugin::getInstance();
