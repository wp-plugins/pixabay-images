<?php
$tpl_form = '
    <link rel="stylesheet" href="'._PIXABAY_IMAGES_STATIC_URL.'base.css" />
    <div id="tpl_form">{html_form}</div>
';

$tpl_item = '
	<div class="tpl-item">
		<a class="open_lightbox" data-post_id="{post_id}" data-media_tab="{media_tab}" data-width="{imageWidth}" data-height="{imageHeight}" data-width_640="{webformatWidth}" data-height_640="{webformatHeight}" data-user="{user}" data-tags="{tags}" data-page_url="http://pixabay.com/p-{id}/" target="_blank" href="{webformatURL}">
			<img alt="{tags}" src="{previewURL}" />
		</a>
		<div class="info">
            <a href="#" class="insert_640 direct" data-post_id="{post_id}" data-media_tab="{media_tab}" data-image_src="{webformatURL}" data-image_tags="{tags}" data-page_url="http://pixabay.com/p-{id}/" data-image_user="{user}">Insert {webformatWidth}x{webformatHeight}</a>
		</div>
	</div>
';

$tpl_navi = '<div class="tpl-navi">{tpl_navi}</div>';

$tpl_info_init_search ='<div class="tpl_info_init_search">{info_init_search}</div>';

function tpl_get_html( $tpl, $search_replace ) {
	$html = $tpl;
	foreach ($search_replace as $search=>$replace) :
		$str_search = '{'.$search.'}';
		$html = str_replace($str_search, $replace, $html);
	endforeach;
	return $html;
}
?>
