<?php

// some helpful functional friends
// thanks to Jill Burrows
// http://jburrows.wordpress.com/
// I reversed the arguments on the helpers to match the clojure way
// Also, thanks for the hint to use references to help performance!

function compose($f, $g) {
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
function map($f, $data) {
  return array_map($f, $data);
}

// Convenience wrapper for filtering arrays
function filter($f, $data) {
  return array_filter($data, $f);
}

// Convenience wrapper for reducing arrays
function fold($f, $data, $initial = null) {
  return array_reduce($data, $f, $initial);
}

// Thanks Jill!

// This is a handy one from clojure
// Does a map and then concatenates all the results
function mapcat($function, $list) {
	$res = map($function, $list);
	$init = '';
	return fold(function($col, $val) {
		return $col . $val;
	}, $res, $init); 
}

// Since model results are returned as arrays, we want to be able to get
// results whose cols (key) is what we are looking for (val)
function array_filter_search($array, $key, $val) {
	// wonderful functional code possible here
	return filter(function($element) use ($key, $val) {
		return (isset($element[$key]) && ($element[$key] == $val));
	}, $array);
}

// another great clojure function
// key_seq is the magic bit - array('this', 'guy', 'what') key_seq refers to
// $array['this']['guy']['what']
// although to avoid the agony of too much copying, this is sadly done mutably
function assoc_in($array, $key_seq, $val) {
	$keystr = mapcat(function($key) {
			return "[{$key}]";
		}, $key_seq);
	$array{$keystr} = $val;
}

function first($array) {
	$rev = array_reverse($array);
	return array_pop($rev);
}

// borrowed from Ruby
function group_by($array, $by) {
	// if $by is a function, we do it this way
	if (is_object($by) && is_callable($by)) {
		return fold(function(&$coll, $elem) use ($by) {
			$coll[$by($elem)] = $elem;
			return $coll;
		}, $array, array());
	} else {
	// otherwise, we do it this way (assuming $by is a key)
		return fold(function($coll, $elem) use ($by) {
			$coll[$elem[$by]][] = $elem;
			return $coll;
		}, $array, array());

	}
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
/**
 * looks to see if $array has $key and the value isn't empty
 * basically to replace this construct which is making me insane using it
 * over and over:
 * if (isset($array[$key]) && !empty($array[$key]))
 */
function set_not_empty($array, $key) {
	return (isset($array[$key]) && !empty($array[$key]));
}
