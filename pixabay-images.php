<?php

/*
Plugin Name: Pixabay Images
Plugin URI: https://pixabay.com/blog/posts/p-36/
Description: Find quality public domain images from Pixabay and upload them with just one click.
Version: 2.12
Author: Simon Steinberger
Author URI: https://pixabay.com/users/Simon/
License: GPLv2
*/


// i18n
function pixabay_images_load_textdomain() { load_plugin_textdomain('pixabay_images', false, dirname(plugin_basename(__FILE__ )).'/langs/'); }
add_action('plugins_loaded', 'pixabay_images_load_textdomain');


// add settings
include(plugin_dir_path(__FILE__).'settings.php');


function pixabay_images_enqueue_jquery() { wp_enqueue_script('jquery'); }
add_action('admin_enqueue_scripts', 'pixabay_images_enqueue_jquery');


// add tab to media upload window
function media_upload_tabs_handler($tabs) { $tabs['pixabaytab'] = __('Pixabay Images', 'pixabay_images'); return $tabs; }
add_filter('media_upload_tabs', 'media_upload_tabs_handler');


// add button next to "Add Media"
$pixabay_images_settings = get_option('pixabay_images_options');
if (!$pixabay_images_settings['button'] | $pixabay_images_settings['button']=='true') {
    function media_buttons_context_handler($editor_id='') { return '<a href="'.add_query_arg('tab', 'pixabaytab', esc_url(get_upload_iframe_src())).'" id="'.esc_attr($editor_id).'-add_media" class="thickbox button" title="'.esc_attr__('Pixabay Images', 'pixabay_images').'"><img style="position:relative;top:-2px" src="'.plugin_dir_url(__FILE__).'favicon.png'.'"> Pixabay</a>'; }
    add_filter('media_buttons_context', 'media_buttons_context_handler');
}


// media tab action
// function must begin with "media_" so wp_iframe() adds media css styles
function media_pixabay_images_tab() {
    media_upload_header();
    $pixabay_images_settings = get_option('pixabay_images_options');
	?>
        <style scope>
            html, body { background: #fff; }
            #paginator .button { margin: 0 5px 5px 0; }
            .thumb { float: left; height: 150px; line-height: 150px; text-align: center; margin: 0 10px 2px 0; }
            .thumb img { vertical-align: middle; }
            .thumb.small { height: 110px; line-height: 110px; text-align: center; margin: 0 8px 2px 0; }
            .thumb.small img { height: auto !important; width: auto !important; max-height: 110px; max-width: 110px; vertical-align: middle; }
            .thumb .preview img { max-height: none; max-width: none; }
            .preview {
                z-index: 99999; position: absolute; background: #fff; border: 10px solid #fff; border: 10px solid rgba(255,255,255,.95);
                -webkit-background-clip: padding-box; background-clip: padding-box;
                border-radius: 2px; -moz-border-radius: 2px; -webkit-border-radius: 2px;
                -webkit-box-shadow: 0 1px 8px rgba(0,0,0,.3); -moz-box-shadow: 0 1px 8px rgba(0,0,0,.3); box-shadow: 0 1px 8px rgba(0,0,0,.3);
                line-height: 1;
            }
            .preview img { position: absolute; left: 0; top: 0; }
        </style>
        <div style="padding:10px 15px 25px">
            <form id="pixabay_images_form" style="margin:0">
                <p><input id="q" type="text" value="" style="width:100%;max-width:500px;padding:7px 9px"></p>
                <p>
                    <label style="margin-right:15px"><input type="checkbox" id="filter_photos"<?= $pixabay_images_settings['image_type']=='clipart'?'':' checked="checked"'; ?>><?= _e('Photos', 'pixabay_images'); ?></label>
                    <label style="margin-right:20px"><input type="checkbox" id="filter_cliparts"<?= $pixabay_images_settings['image_type']=='photo'?'':' checked="checked"'; ?>><?= _e('Cliparts', 'pixabay_images'); ?></label>
                    <span style="margin-right:20px">|</span>
                    <label style="margin-right:15px"><input type="checkbox" id="filter_horizontal"<?= $pixabay_images_settings['orientation']=='vertical'?'':' checked="checked"'; ?>><?= _e('Horizontal', 'pixabay_images'); ?></label>
                    <label style="margin-right:25px"><input type="checkbox" id="filter_vertical"<?= $pixabay_images_settings['orientation']=='horizontal'?'':' checked="checked"'; ?>><?= _e('Vertical', 'pixabay_images'); ?></label>
                    <a href="options-general.php?page=pixabay_images_settings" target="_blank"><?= _e('Settings', 'pixabay_images'); ?></a>
                </p>
                <input id="submit_search" class="button" type="submit" value="<?= _e('Search', 'pixabay_images'); ?>">
            </form>
            <div id="pixabay_results" style="margin-top:25px;padding-top:25px;border-top:1px solid #ddd"></div>
        </div>
        <script>
            function escapejs(s){return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,"\\'");}
            // hoverIntent r7
            (function(e){e.fn.hoverIntent=function(t,n,r){var i={interval:100,sensitivity:7,timeout:0};if(typeof t==="object"){i=e.extend(i,t)}else if(e.isFunction(n)){i=e.extend(i,{over:t,out:n,selector:r})}else{i=e.extend(i,{over:t,out:t,selector:n})}var s,o,u,a;var f=function(e){s=e.pageX;o=e.pageY};var l=function(t,n){n.hoverIntent_t=clearTimeout(n.hoverIntent_t);if(Math.abs(u-s)+Math.abs(a-o)<i.sensitivity){e(n).off("mousemove.hoverIntent",f);n.hoverIntent_s=1;return i.over.apply(n,[t])}else{u=s;a=o;n.hoverIntent_t=setTimeout(function(){l(t,n)},i.interval)}};var c=function(e,t){t.hoverIntent_t=clearTimeout(t.hoverIntent_t);t.hoverIntent_s=0;return i.out.apply(t,[e])};var h=function(t){var n=jQuery.extend({},t);var r=this;if(r.hoverIntent_t){r.hoverIntent_t=clearTimeout(r.hoverIntent_t)}if(t.type=="mouseenter"){u=n.pageX;a=n.pageY;e(r).on("mousemove.hoverIntent",f);if(r.hoverIntent_s!=1){r.hoverIntent_t=setTimeout(function(){l(n,r)},i.interval)}}else{e(r).off("mousemove.hoverIntent",f);if(r.hoverIntent_s==1){r.hoverIntent_t=setTimeout(function(){c(n,r)},i.timeout)}}};return this.on({"mouseenter.hoverIntent":h,"mouseleave.hoverIntent":h},i.selector)}})(jQuery)

            var $=jQuery, post_id=<?=absint($_REQUEST['post_id']) ?>,
                lang='<?= $pixabay_images_settings['language']?$pixabay_images_settings['language']:substr(get_locale(), 0, 2) ?>',
                per_page=<?=$pixabay_images_settings['per_page']?$pixabay_images_settings['per_page']:30 ?>,
                form = $('#pixabay_images_form'), hits, cache, resizeTimer, q, image_type, orientation;

            function resized() { if ($(window).width() < 768) $('.thumb').addClass('small'); else $('.thumb').removeClass('small'); }
            setTimeout(function(){ $(window).resize(); }, 300);
            $(window).resize(function() { clearTimeout(resizeTimer); resizeTimer = setTimeout(resized, 250); });

            form.submit(function(e){
                e.preventDefault();
                cache = {};
                q = $('#q', form).val();
                if ($('#filter_photos', form).is(':checked') && !$('#filter_cliparts', form).is(':checked')) image_type = 'photo';
                else if (!$('#filter_photos', form).is(':checked') && $('#filter_cliparts', form).is(':checked')) image_type = 'clipart';
                else image_type = 'all';
                if ($('#filter_horizontal', form).is(':checked') && !$('#filter_vertical', form).is(':checked')) orientation = 'horizontal';
                else if (!$('#filter_horizontal', form).is(':checked') && $('#filter_vertical', form).is(':checked')) orientation = 'vertical';
                else orientation = 'all';
                call_api(q, 1);
            });

            function call_api(q, p){
                if (p in cache)
                    render_px_results(q, p, cache[p]);
                else {
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', '//pixabay.com/api/?username=WPPlugin&key=a70dc9ab130236b9e67c&response_group=high_resolution&lang='+lang+'&image_type='+image_type+'&orientation='+orientation+'&per_page='+per_page+'&page='+p+'&search_term='+encodeURIComponent(q));
                    xhr.onreadystatechange = function(){
                        if (this.status == 200 && this.readyState == 4) {
                            var data = JSON.parse(this.responseText);
                            if (!(data.totalHits > 0)) {
                                $('#pixabay_results').html('<div style="color:#d71500;font-size:16px">No hits</div>');
                                return false;
                            }
                            cache[p] = data;
                            render_px_results(q, p, data);
                        }
                    };
                    xhr.send();
                }
                return false;
            }

            function render_px_results(q, p, data){
                hits = data['hits']; // store for upload click
                pages = Math.ceil(data.totalHits/per_page);
                var s = '';
                $.each(data.hits, function(k, v) {
                    s += '<div class="thumb" data-idx="'+k+'"><img style="width:'+v.previewWidth+'px;height:'+v.previewHeight+'px;" src="'+v.previewURL+'"></div>';
                });
                s += '<div style="clear:both;height:30px"></div><div id="paginator" style="text-align:center">';
                if (p==1)
                    s += '<span class="button disabled">Prev</span>';
                else
                    s += '<a href="#" onclick="return call_api(\''+escapejs(q)+'\', '+(p-1)+');" class="button">Prev</a>';
                for (i=1; i < pages+1; i++) {
                    s += '<a href="#" onclick="return call_api(\''+escapejs(q)+'\', '+i+');" class="button'+(p==i?' disabled':'')+'">'+i+'</a>';
                }
                if (p==pages)
                    s += '<span class="button disabled">Next</span>';
                else
                    s += '<a href="#" onclick="return call_api(\''+escapejs(q)+'\', '+(p+1)+');" class="button">Next</a>';
                s += '</div>';
                $('#pixabay_results').html(s);
                resized();

                var thumb, idx, offset, x, y, scroll_l, scroll_t, l, t, img, ratio, width640, heigh640, width1280, height1280;
                $('.thumb').hoverIntent({
                    timeout: 150,
                    interval: 120,
                    over: function(e){
                        thumb = $(this);
                        idx = thumb.data('idx');
                        scroll_l = $(window).scrollLeft();
                        scroll_t = $(window).scrollTop();
                        img = thumb.find('img');
                        offset = img.offset();
                        x = offset.left - scroll_l;
                        y = offset.top - scroll_t;
                        ratio = hits[idx].imageWidth / hits[idx].imageHeight;
                        longest_side = (hits[idx].imageWidth > hits[idx].imageHeight) ? hits[idx].imageWidth : hits[idx].imageHeight;
                        if (ratio > 1) {
                            width640 = 640, height640 = parseInt(640/ratio);
                            if (longest_side >= 1280)
                                width1280 = 1280, height1280 = parseInt(1280/ratio);
                            else
                                width1280 = longest_side, height1280 = parseInt(longest_side/ratio);
                        } else {
                            height640 = 640, width640 = parseInt(640*ratio);
                            if (longest_side >= 1280)
                                height1280 = 1280, width1280 = parseInt(1280*ratio);
                            else
                                height1280 = longest_side, width1280 = parseInt(longest_side*ratio);
                        }

                        preview = $('<div data-idx="'+idx+'" class="preview">\
                            <div title="<?= _e('Insert image') ?>" style="padding:4px 5px 6px;margin:0 0 5px;text-align:left;border-bottom:1px solid #ddd">\
                                <a href="#" class="upload 150px" style="margin-right:15px">'+img.width()+' x '+img.height()+'</a>\
                                <a href="#" class="upload 640px" style="margin-right:15px">'+width640+' x '+height640+'</a>\
                                <a href="#" class="upload 1280px">'+width1280+' x '+height1280+'</a>\
                            </div>\
                            <a title="<?= _e('Insert image') ?>" href="#" class="upload 640px" style="display:block;margin:auto;position:relative;width:'+parseInt(width640/2)+'px;height:'+parseInt(height640/2)+'px;">\
                                <img src="'+img.attr('src')+'" style="width:'+parseInt(width640/2)+'px !important;height:'+parseInt(height640/2)+'px !important;">\
                                <img src="'+hits[idx].webformatURL+'" style="width:'+parseInt(width640/2)+'px !important;height:'+parseInt(height640/2)+'px !important;">\
                            </a>\
                            <div style="padding:6px 5px 4px;margin:5px 0 0;text-align:left;border-top:1px solid #ddd"><?= _e('CC0 Image by', 'pixabay_images'); ?> <a href="https://pixabay.com/users/'+hits[idx].user+'/" target="_blank">'+hits[idx].user+'</a> / <a href="https://pixabay.com/'+lang+'/photos/?image_type='+image_type+'&orientation='+orientation+'&q='+escapejs(q)+'" target="_blank">Pixabay</a></div>\
                        </div>');
                        thumb.append(preview);

                        if (x < $(window).width()/2) l = x; else l = x - preview.outerWidth() + img.outerWidth();
                        if (y < $(window).height()/2) {
                            t = y + img.outerHeight() - 5;
                            if (t+preview.outerHeight() > $(window).height()) t = $(window).height() - preview.outerHeight() - 5;
                        } else {
                            t = y - preview.outerHeight() + 5;
                            if (t < 0) t = 5;
                        }
                        preview.css({'left':l + scroll_l, 'top':t + scroll_t});
                    },
                    out: function(e){
                        $('.preview').remove();
                    }
                });
            }

            $(document).on('click', '.upload', function() {
                var idx = $('.preview').data('idx'), image_url;
                image_url = $(this).hasClass('150px') ? hits[idx].previewURL : $(this).hasClass('1280px') ? hits[idx].largeImageURL : hits[idx].webformatURL;
                $('.preview').html('Uploading image ...');
                $.post('.', { pixabay_upload: "1", image_url: image_url, image_user: hits[idx].user, q: q, wpnonce: '<?= wp_create_nonce('pixabay_images_security_nonce'); ?>' }, function(data){
                    if (parseInt(data) == data)
                        window.location = 'media-upload.php?type=image&tab=library&post_id='+post_id+'&attachment_id='+data;
                    else
                        alert(data);
                });
                return false;
            });
        </script>
    <?php
}
function media_upload_pixabaytab_handler() { wp_iframe('media_pixabay_images_tab'); }
add_action('media_upload_pixabaytab', 'media_upload_pixabaytab_handler');


if (isset($_POST['pixabay_upload'])) {
    # "pluggable.php" is required for wp_verify_nonce() and other upload related helpers
    if (!function_exists('wp_verify_nonce'))
        require_once(ABSPATH.'wp-includes/pluggable.php');

	$nonce = $_POST['wpnonce'];
	if (!wp_verify_nonce($nonce, 'pixabay_images_security_nonce')) {
        die('Error: Invalid request.');
		exit;
	}

    $post_id = absint($_REQUEST['post_id']);
    $pixabay_images_settings = get_option('pixabay_images_options');

    // parse image_url
    $url = str_replace('https:', 'http:', $_POST['image_url']);
    $parsed_url = parse_url($url);
    if(strcmp($parsed_url['host'], 'pixabay.com')) {
        die('Error: Invalid host in URL (must be pixabay.com)');
    }

    // get image file
	$response = wp_remote_get($url);
	if (is_wp_error($response)) die('Error: '.$response->get_error_message());

	$q_tags = explode(' ' , $_POST['q']);
	array_splice($q_tags, 2);
	foreach ($q_tags as $k=>$v) {
		// remove ../../../..
		$v = str_replace("..", "", $v);
		$v = str_replace("/", "", $v);
		$q_tags[$k] = trim($v);
	}
    $path_info = pathinfo($url);
	$file_name = sanitize_file_name(implode('_', $q_tags).'_'.time().'.'.$path_info['extension']);

	$wp_upload_dir = wp_upload_dir();
	$image_upload_path = $wp_upload_dir['path'];

	if (!is_dir($image_upload_path)) {
		if (!@mkdir($image_upload_path, 0777, true)) die('Error: Failed to create upload folder '.$image_upload_path);
	}

	$target_file_name = $image_upload_path . '/' . $file_name;
	$result = @file_put_contents($target_file_name, $response['body']);
	unset($response['body']);
	if ($result === false) die('Error: Failed to write file '.$target_file_name);

	// are we dealing with an image
    require_once(ABSPATH.'wp-admin/includes/image.php');
	if (!wp_read_image_metadata($target_file_name)) {
		unlink($target_file_name);
		die('Error: File is not an image.');
	}

	$image_title = ucwords(implode(', ', $q_tags));
    $attachment_caption = '';
    if (!$pixabay_images_settings['attribution'] | $pixabay_images_settings['attribution']=='true')
        $attachment_caption = '<a href="https://pixabay.com/users/'.htmlentities($_POST['image_user']).'/">'.htmlentities($_POST['image_user']).'</a> / Pixabay';

    // insert attachment
	$wp_filetype = wp_check_filetype(basename($target_file_name), null);
	$attachment = array(
        'guid' => $wp_upload_dir['url'].'/'.basename($target_file_name),
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => preg_replace('/\.[^.]+$/', '', $image_title),
        'post_status' => 'inherit'
	);
	$attach_id = wp_insert_attachment($attachment, $target_file_name, $post_id);
	if ($attach_id == 0) die('Error: File attachment error');

	$attach_data = wp_generate_attachment_metadata($attach_id, $target_file_name);
	$result = wp_update_attachment_metadata($attach_id, $attach_data);
	if ($result === false) die('Error: File attachment metadata error');

	$image_data = array();
	$image_data['ID'] = $attach_id;
	$image_data['post_excerpt'] = $attachment_caption;
	wp_update_post($image_data);

	echo $attach_id;
    exit;
}

?>
