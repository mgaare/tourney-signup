<?php

// Since model results are returned as arrays, we want to be able to get
// results whose cols (key) is what we are looking for (val)
function array_filter_search($array, $key, $val) {
	// wonderful functional code possible here
	return array_filter($array, function($element) use ($key, $val) {
		return (isset($lement[$key]) && ($element[$key] == $val));
	});
}

function array_first($array) {
	return array_pop(array_reverse($array));
}

function include_protection($file) {
	global $include_protection;
	
	if (!(isset($include_protection) && $include_protection)) {
		error_log('Attempt to view template directly: ' . $file);
		die();
	}
}