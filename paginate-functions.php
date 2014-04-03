<?php
function curPageURL_pixa() {
$pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
if ($_SERVER["SERVER_PORT"] != "80")
{
    $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
}
else
{
    $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
}
return $pageURL;
}

// Function to generate pagination array - that is a list of links for pages navigation .
function paginate ($base_url, $query_str, $total_pages, $current_page, $paginate_limit)
{
    // Array to store page link list
    $page_array = array ();
    // Show dots flag - where to show dots?
    $dotshow = true;
    // walk through the list of pages

	$preview_page = ($current_page > 1) ? ($current_page-1) : 0;
	if ($preview_page > 0) {
		$page_array[0]['url'] = add_query_arg( $query_str, $preview_page, $base_url);
		$page_array[0]['text'] = '<span class="prev">&larr;</span>';
	}

    for ( $i = 1; $i <= $total_pages; $i ++ )
    {
       // If first or last page or the page number falls
       // within the pagination limit
       // generate the links for these pages
       if ($i == 1 || $i == $total_pages ||
             ($i >= $current_page - $paginate_limit &&
             $i <= $current_page + $paginate_limit) )
       {
          // reset the show dots flag
          $dotshow = true;
          // If it's the current page, leave out the link
          // otherwise set a URL field also
          if ($i != $current_page)
			$page_array[$i]['url'] = add_query_arg( $query_str, $i, $base_url);
			$page_array[$i]['text'] = strval ($i);
       }
       // If ellipses dots are to be displayed
       // (page navigation skipped)
       else if ($dotshow == true)
       {
           // set it to false, so that more than one
           // set of ellipses is not displayed
           $dotshow = false;
           $page_array[$i]['text'] = "...";
       }
    }

	$next_page = ($current_page < $total_pages) ? ($current_page+1) : $total_pages;
	if ($next_page > $current_page) {
		$i++;
		$page_array[$i]['url'] = add_query_arg( $query_str, $next_page, $base_url);
		$page_array[$i]['text'] = '<span class="next">&rarr;</span>';
	}

	//$last_page = ($current_page < $total_pages) ? ($total_pages+1) : $total_pages;

    // return the navigation array
    return $page_array;
}

function get_paginate($base_url, $query_str, $total_pages, $current_page, $paginate_limit=5) {
	$pages = paginate ($base_url, $query_str, $total_pages, $current_page, $paginate_limit);
	$html = '';
	foreach ($pages as $page) :
		if (isset ($page['url'])) {
			$html .= '<a href="'.$page['url'].'">'.$page['text'].'</a>';
        } else if ($page['text'] == $current_page) {
			$html .= '<span class="current">'.$page['text'].'</span>';
		} else {
			$html .= '<span>'.$page['text'].'</span>';
		}
	endforeach;
    return $html;
}
?>