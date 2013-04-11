jQuery(document).ready(function($) {

	var ajax_image = $('#imgsource').attr('src');
    $(document).on('click', '.insert_640', function() {
        var image_src = $(this).data("image_src"),
            image_tags = $(this).data("image_tags"),
            page_url =  $(this).data("page_url"),
            image_user =  $(this).data("image_user"),
            load_media_tab = $(this).data("media_tab"),
            post_id = $(this).data("post_id");

        $('#lightbox').show();
        $('#imgsource').attr('src', ajax_image);
        $('#content').html('<img class="ajaxloading" src="' + ajax_image + '" />');

        $.post('.', { pixabay_upload: "1", source_url: image_src, tags: image_tags, page_url: page_url, image_user: image_user, post_id: post_id },
            function(data) {
                var obj = jQuery.parseJSON(data);
                if ( obj.result === 0 ) {
                    alert(obj.msg);
                } else {
                    var url = 'media-upload.php?type=image&tab='+load_media_tab+'&post_id='+post_id+'&attachment_id='+obj.msg;
                    window.location = url;
                }
            });
        return false;
    });

	$('.open_lightbox').click(function(e) {
		var image_href = $(this).attr("href"),
            image_tags = $(this).data("tags"),
            page_url = $(this).data("page_url"),
            image_user = $(this).data("user"),
            image_width_640 = $(this).data("width_640"),
            image_height_640 = $(this).data("height_640"),
            image_width = $(this).data("width"),
            image_height = $(this).data("height"),
			load_media_tab = $(this).data("media_tab"),
            post_id = $(this).data("post_id"),
            infohtml = '\
                <div id="navigation">\
                    <div style="float:right;margin:11px 15px 0 0">By <a href="http://pixabay.com/users/'+image_user+'/" target="_blank" onclick="var event=arguments[0] || window.event; try { event.stopPropagation(); } catch(e) { window.event.cancelBubble = true; }">'+image_user+'</a> / Full resolution <a href="'+page_url+'" target="_blank" onclick="var event=arguments[0] || window.event; try { event.stopPropagation(); } catch(e) { window.event.cancelBubble = true; }">'+image_width+'x'+image_height+'</a></div>\
                    <a href="#" class="button insert_640" data-media_tab="' + load_media_tab + '" data-post_id="' + post_id + '" data-image_src="' + image_href + '" data-image_tags="' + image_tags + '" data-page_url="' + page_url + '" data-image_user="' + image_user + '"><b>Insert</b></a>\
                    <a href="'+page_url+'" target="_blank" class="button" onclick="var event=arguments[0] || window.event; try { event.stopPropagation(); } catch(e) { window.event.cancelBubble = true; }" title="Full resolution image on Pixabay">Visit page</a>\
                    <a href="#" class="button" onclick="$(\'#lightbox\').hide();" title="Cancel">X</a>\
                </div>';

        // place href as img src value
        $('#content').html('<img id="imgajax" class="ajaxloading" src="'+ajax_image+'" /><img id="imgsource" style="display: none" src="' + image_href + '" />' + infohtml);
        $('#lightbox').show();
		$('#imgsource').attr('src', image_href).load(function() {
			$('#imgajax').hide();
			$('#imgsource').show();

            // fix height of 640px image (substract 50 px for navigation bar)
            if ($('#TB_iframeContent', parent.document).length > 0)
                popup_height = $('#TB_iframeContent', parent.document).height() - 50;
            else
                popup_height = $('.media-iframe iframe', parent.document).first().height() - 50;
            if (image_height_640 > popup_height)
                $('#imgsource').css({'height': popup_height, 'margin': 0});
            else {
                var tb_margin = (popup_height-image_height_640)/2
                $('#imgsource').css({'height': image_height_640, 'margin-top': tb_margin, 'margin-bottom': tb_margin});
            }
		});
        return false;
	});

	// click anywhere on the page to close the lightbox
    $('#lightbox').click(function() { $('#lightbox').hide(); });
});