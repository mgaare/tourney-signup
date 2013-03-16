<?php

// some helpful functional friends
// thanks to Jill Burrows
// http://jburrows.wordpress.com/
// I reversed the arguments on the helpers to match the clojure way
// Also, thanks for the hint to use references to help performance!

function compose(&$f, &$g) {
  // Return the composed function
  return function() use($f,$g) {
    // Get the arguments passed into the new function
    $x = func_get_args();
    // Call the function to be composed with the arguments
    // and pass the result into the first function.
    return $f(call_user_func_array($g, $x));
  };
}

// Convenience wrapper for mapping
function map(&$f, &$data) {
  return array_map($f, $data);
}

// Convenience wrapper for filtering arrays
function filter(&$f, &$data) {
  return array_filter($data, $f);
}

// Convenience wrapper for reducing arrays
function fold(&$f, &$data, &$initial = null) {
  return array_reduce($data, $f, $initial);
}

// Thanks Jill!

// This is a handy one from clojure
// Does a map and then concatenates all the results
function mapcat(&$function, &$list) {
	return fold(function($col, $val) {
		return $col . $val; },
		map($function, $list), '');
}

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

function map_print($array, $function) {
	array_walk(
		array_map($array, $function),
		'print_r');
}