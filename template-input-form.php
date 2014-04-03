<?php
function _print_form_text($name, $param, $selected) {
	return '<input type="text" name="'.$name.'" value="'.$selected.'" >';
}

function _print_form_radio($name, $param, $selected) {
	$str = '';
	foreach ($param as $p) :
		if ($selected ==  $p) $s='checked="checked"'; else $s='';
		$str .= '<label><input '.$s.' type="radio" name="'.$name.'" value="'.$p.'" />'.ucfirst($p).'</label>';
	endforeach;
	return $str;
}

function byrev_print_form($script, $request_parameters_info, $pixabay_api_request) {
	$echo = '<form name="input" action="'.$script.'" method="get">';
	foreach ($request_parameters_info as $name=>$info):
		$input_type = $info['type'];
		$func_form = "_print_form_".$input_type;
		if (function_exists($func_form)) {
			$input_name = 'pixabay-request['.$name.']';
			$echo .= '<div class="input-form">';
			$echo .= '<div class="input-name">'.$info['title']. '</div>';
			$echo .=  $func_form($input_name, $info['values'], $pixabay_api_request[$name]);
			$echo .=  '</div>';
		} else {
			$echo .=  'INPUT TYPE: '.$input_type.' - '.$name.'<br />';
		}
		
	endforeach;
	$echo .= '<input type="hidden" value="'._EDIT_POST_ID.'" name="post_id" />';
	$echo .=  '<div style="clear: both;"></div><input type="hidden" name="tab" value="pixabaytab" /><input class="button" type="submit" value="Search Images"></form>';
	return $echo;
}
?>