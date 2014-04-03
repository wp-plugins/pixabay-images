<?php

if (!is_admin()) die();

if (isset($_POST['pixabay_upload'])) {
	if ( !isset($_POST['post_id']) ) die('Cheatin uh?');
	$post_id = $_POST['post_id'];
	if ( !is_numeric( $post_id ) ) die('Cheatin uh?');

	# Function from wp-pluggable-for-pixabay.php is imported from pluggable.php
	# Without this current_user_can not working here!
	# This should not interefere with other plugins. This file is included only when the insert button is pressed.
	require_once('wp-pluggable-for-pixabay.php');

	if ( !current_user_can('edit_post', $post_id) ) die('Cheatin uh?');

	$source_url = $_POST['source_url'];
	$tags = $_POST['tags'];
	$page_url = $_POST['page_url'];
	$image_user = $_POST['image_user'];

    // upload image file
	$response = wp_remote_get( $source_url );
	if( is_wp_error( $response ) ) {
	   $error_message = $response->get_error_message();
	   die(json_response(0, "Something went wrong: ". $error_message));
	}
	$data = $response['body'];

	$path_parts = pathinfo($source_url);
	$tag_list = explode(',' , $tags);
	array_splice($tag_list, 10);
	foreach ($tag_list as $index=>$tag) $tag_list[$index] = trim($tag);
	$file_name = $tag_list[1].'_'.time(). '.' . $path_parts['extension'];

	$hash_upload = md5($file_name);
	$wp_upload_dir = wp_upload_dir();
	$image_upload_path = $wp_upload_dir['path'] . '/pixabay/'.$hash_upload[0];

	if (!is_dir($image_upload_path)) {
		if (!@mkdir($image_upload_path, 0777, true)) die(json_response(0, 'ERROR - Failed to create upload folder: '.$image_upload_path));
	}

	$target_file_name = $image_upload_path . '/' . $file_name;

	$result = @file_put_contents($target_file_name, $data);
	unset($data);
	if ($result === FALSE) die(json_response(0, "ERROR WRITE FILE: ".$target_file_name));
	$image_title = ucwords(implode(', ', $tag_list));
    $attachment_caption = ''.$image_user.' / <a href="'.$page_url.'">Pixabay</a>';
	wp_insert_attachment_to_post($target_file_name, $image_title, $attachment_caption, $post_id);

	exit;
}

function json_response($result, $msg) {
	return json_encode(array('result'=>$result, 'msg'=>$msg));
}

function wp_insert_attachment_to_post($filename, $image_title, $attachment_caption, $parent_post_id=0) {
	$wp_filetype = wp_check_filetype(basename($filename), null );
	$wp_upload_dir = wp_upload_dir();
	$attachment = array(
	 'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ),
	 'post_mime_type' => $wp_filetype['type'],
	 'post_title' => preg_replace('/\.[^.]+$/', '', $image_title),
	 'post_status' => 'inherit'
	);
	$attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );
	if ($attach_id == 0) die(json_response(0, "wp_insert_attachment() ERROR"));

	require_once(ABSPATH . 'wp-admin/includes/image.php');
	$attach_data = wp_generate_attachment_metadata($attach_id, $filename);
	$result = wp_update_attachment_metadata( $attach_id, $attach_data );

	if ($result === FALSE) die(json_response(0, "wp_update_attachment_metadata() ERROR"));

	$image_data = array();
	$image_data['ID'] = $attach_id;
	$image_data['post_excerpt'] = $attachment_caption;
	wp_update_post( $image_data);

	echo json_response(1, $attach_id);
}
?>
