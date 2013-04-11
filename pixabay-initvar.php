<?php
define('_PIXABAY_IMAGES_QUERY_CACHE_EXPIRE_TIME', 86400); # 1 day expire cache
define('_PIXABAY_IMAGES_RESULT_PER_PAGE', 26);

/************* CREATE CACHE FOLDER ***************/
$_wp_upload_dir = wp_upload_dir(); 
$_wp_upload_basedir = $_wp_upload_dir['basedir']; 
define('_PIXABAY_IMAGES_QUERY_CACHE_FOLDER', $_wp_upload_basedir.'/cache~pixabay');

if (!is_dir(_PIXABAY_IMAGES_QUERY_CACHE_FOLDER)) {
    @mkdir(_PIXABAY_IMAGES_QUERY_CACHE_FOLDER, 0705);
}

/************* INIT REQUEST *************/
global $pixabay_api_default_query;
$pixabay_api_default_query = array(
	'username' => 'WPPlugin',
	'key' => 'a70dc9ab130236b9e67c',
	'search_term' => '',
	'lang' => 'en',
	'image_type' => 'all',
	'orientation' => 'all',
	'order' => 'popular',
	'page' => 1,
	'per_page' => _PIXABAY_IMAGES_RESULT_PER_PAGE,
);

global $pixabay_api_request;
if (isset($_REQUEST['pixabay-request'])) {
	$pixabay_api_request = $_REQUEST['pixabay-request'];
	foreach ($pixabay_api_default_query as $name=>$value):
		if (!isset($pixabay_api_request[$name])) {
			$pixabay_api_request[$name] =  $value;
		}
		//
	endforeach;
	define('_PIXABAY_NEW_REQUEST', true);
} else {
	$pixabay_api_request = $pixabay_api_default_query;
	define('_PIXABAY_NEW_REQUEST', false);
}

/************* LAST QUERY **************/
global $byrev_api_pixabay_last_query;
$byrev_api_pixabay_last_query = "";

/************* PRINT PARAMETERS **************/
global $request_parameters_info;
$request_parameters_info = array (
	'search_term' => array('type'=>'text', 'values' => "", 'title'=> 'Search'),
	#'lang' => array('type'=>'select', 'values' => array('en', 'id','cs','de','es','fr','it','nl','no','hu','ru','pl','pt','ro','fi','sv','tr','ja','ko','zh') , 'title'=> 'Language'),
	'image_type' => array('type'=>'radio', 'values' => array("all", "photo", "clipart" ), 'title'=> 'Image type'),
	'orientation' => array('type'=>'radio', 'values' => array("all", "landscape", "portrait"), 'title'=> 'Orientation') ,
);

/************* POST ID **************/
if (!isset($_REQUEST['post_id'])) $_REQUEST['post_id'] = '0';
define('_EDIT_POST_ID', $_REQUEST['post_id']);
?>