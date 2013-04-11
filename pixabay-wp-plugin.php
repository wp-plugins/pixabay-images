<?php

/*
Plugin Name: Pixabay Images
Plugin URI: http://pixabay.com/en/blog/posts/pixabay-plugin-for-wordpress-36/
Description: Find quality CC0 public domain images for commercial use, and add them to your blog with just a click. Attribution is not required.
Version: 1.0
Author: Emilian Robert Vicol / Pixabay
Author URI: http://profiles.wordpress.org/byrev/
License:  GPLv2
*/

define('_PIXABAY_IMAGES_PLUGIN_VERSION', '1.0');
define('_PIXABAY_IMAGES_STATIC_URL', plugin_dir_url(__FILE__).'static/' );
define('_PIXABAY_IMAGES_DB_OPTION_NAME', 'pixabay_images');

#~~~ init options
global $default_pixabay_options;
$default_pixabay_options = array(
	'version'=> _PIXABAY_IMAGES_PLUGIN_VERSION,
);

global $byrev_pixabay_options;
$byrev_pixabay_options = false;
#~~~
add_action( 'admin_init', 'pixabay_scripts_method' );
function pixabay_scripts_method() {
	wp_enqueue_script(
		'pixabay-lightbox',
		_PIXABAY_IMAGES_STATIC_URL . 'pixabay-lightbox.js',
		array( 'jquery' ),
		false,
		true
	);
}

#~~~ run script if post/get option
if (isset($_POST[_PIXABAY_IMAGES_DB_OPTION_NAME])) {
	byrev_pixabay_update_settings($_POST[_PIXABAY_IMAGES_DB_OPTION_NAME]);
}

if (isset($_POST['pixabay_upload'])) {
	byrev_pixabay_load_plugin();
	include('wp-upload-image.php');
	//exit;
}

#~~~
add_action('wp_head', 'pixabay_wp_head');
function pixabay_wp_head() {
?>
<script type="text/javascript">

</script>
<?php
}

#~~~ admin init
add_action('admin_init','byrev_pixabay_load_plugin');
function byrev_pixabay_load_plugin() {
	if(is_admin()) {
		byrev_pixabay_activate();
		global $byrev_pixabay_options;
	}
}


# add tab to media upload window
function pixabay_upload_tab($tabs) {
    $tabs['pixabaytab'] = 'Search Pixabay';
    return $tabs;
}
add_filter('media_upload_tabs', 'pixabay_upload_tab');

function pixabay_media_button($editor_id = '') {
	$img = '<img style="vertical-align:middle;padding:0 3px" src="'._PIXABAY_IMAGES_STATIC_URL.'favicon.ico" />';
	echo '<a href="' . add_query_arg('tab','pixabaytab', esc_url( get_upload_iframe_src() ) ). '" class="thickbox add_media" id="' . esc_attr( $editor_id ) . '-add_media" title="' . esc_attr__( 'Search Pixabay', 'pixabay' ) . '" onclick="return false;">' . sprintf( $img ) . '</a>';
}
add_action('media_buttons', 'pixabay_media_button', 20);

function media_upload_pixabay() {
	byrev_pixabay_load_plugin();
    # wp_iframe() adds css for "media" when callback function has "media_" as prefix (media_dummy)
	function media_dummy() { echo media_upload_header(); include('pixabay-publicdomain.php'); }
    wp_iframe('media_dummy');
}
add_action('media_upload_pixabaytab', 'media_upload_pixabay');


#~~~~~~~~ deactivation and uninstall hook
register_activation_hook( __FILE__ , 'byrev_pixabay_activate' );
register_deactivation_hook( __FILE__ , 'byrev_pixabay_deactivate' );
register_uninstall_hook( __FILE__ ,'byrev_pixabay_uninstall');

function byrev_pixabay_activate() {
global $byrev_pixabay_options, $default_pixabay_options;
	$byrev_pixabay_options = byrev_pixabay_get_settings();

	if ($byrev_pixabay_options['version'] != _PIXABAY_IMAGES_PLUGIN_VERSION) :
		$update_option = array_diff_key($default_pixabay_options, $byrev_pixabay_options);
		if (count($update_option)>0) {
			$byrev_pixabay_options = array_merge($byrev_pixabay_options, $update_option);
			byrev_pixabay_update_settings($byrev_pixabay_options);
		}
	endif;
}

function byrev_pixabay_get_settings() {
global $default_pixabay_options;
	$pixabay_options = get_option(_PIXABAY_IMAGES_DB_OPTION_NAME);
	if ($pixabay_options === false) {
		update_option(_PIXABAY_IMAGES_DB_OPTION_NAME, $default_pixabay_options);
		return $default_pixabay_options;
	}
	return $pixabay_options;
}

function byrev_pixabay_update_settings($store_data) {
	$pixabay_options = get_option(_PIXABAY_IMAGES_DB_OPTION_NAME);
	foreach ($store_data as $key=>$value):
		$pixabay_options[$key] = $value;
	endforeach;
	update_option(_PIXABAY_IMAGES_DB_OPTION_NAME, $pixabay_options);
}

function byrev_pixabay_deactivate() {
	$byrev_pixabay_settings = byrev_pixabay_get_settings();
	# ~~~ change settings for deactivate ... if needed, and save
	byrev_pixabay_update_settings($byrev_pixabay_settings);
}

function byrev_pixabay_uninstall() {
	delete_option( _PIXABAY_IMAGES_DB_OPTION_NAME );
}


?>