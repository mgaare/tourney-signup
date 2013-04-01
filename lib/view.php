<?php

namespace View;

function selectOption($value, $label) {
	return "<option value='{$value}'>{$label}</option>";
}

/**
 * outputs a select form element
 * $params is array with following optional keys:
 * 'id' - id of the element and label
 * 'label' - text for the label
 * 'name' - the select name
 * 'options' - array of options, value and label
 * 'class' - css class of the select element
 */
function select($params = array()) {
	$ret = '';
	$param_options = array('id', 'label', 'name', 'options', 'class');
	// so I don't have to constantly type out the array and index name,
	// we go ahead and set local variables for all the things we care about.
	// either the value from $params, or false if missing
	extract(array_merge(array_fill_keys($param_options, false), $params));
	if ($label) {
		$ret .= "<label";
		if ($id) {
			$ret .= " for='{$id}'";
		}
		$ret .= ">{$label}</label>";
	}
	$ret .= "<select";
	if ($name) {
		$ret .= " name='{$name}'";
	}
	if ($id) {
		$ret .= " id='{$id}";
	}
	if ($class) {
		$ret .= " class='{$class}'";
	}
	$ret .= ">";
	if ($options) {
		$ret .= mapcat(function($option) {
			return selectOption($option['value'], $option['label']);
		}, $options);
	}
	$ret .= "</select>";
	return $ret;
}

/**
 * returns a checkbox form element
 * $params is array with following optional keys:
 * 'id' - id of the element and label ref
 * 'label' - text for the label
 * 'name' - the checkbox name
 * 'class' - css class of the checkbox
 * 'checked' - is it checked
 */
function checkbox($params = array()) {
	$param_options = array('id', 'label', 'name', 'class', 'checked');
	extract(array_merge(array_fill_keys($param_options, false), $params));
	$ret = '';
	if ($label) {
		$ret .= "<label";
		if ($id) {
			$ret .= " for='{$id}'";
		}
		$ret .= ">{$label}</label>";
	}
	$ret .= "<input type='checkbox'";
	if ($name) {
		$ret .= " name='{$name}'";
	}
	if ($id) {
		$ret .= " id='{$id}'";
	}
	if ($class) {
		$ret .= " class='{$class}'";
	}
	if ($checked) {
		$ret .= " checked";
	}
	$ret .= ">";
	return $ret;
}

/** 
 * returns a select form element for years
 * params array:
 * 'num_ahead' = how many years ahead, default 1
 * 'num_behind' = how many years behind, default 50
 * 'class' = optional class of select element
 * 'label' = label name, defaults to 'Year'
 * 'name' = select name, default to 'year'
 * 'id' = css id of select element, default to false
 */
function yearSelect($params = array()) {
	$param_defaults = array('num_ahead' => 1,
							'num_behind' => 50,
						    'class' => false,
							'label' => 'Year',
							'name' => 'year',
							'id' => false);
	$params = array_merge($param_defaults, $params);
	$years = array_reverse(range(date('Y') - $params['num_behind'], 
				   date('Y') + $params['num_ahead']));
	$params['options'] = map(function($year) {
			return array('value' => $year, 'label' => $year); 
		}, $years);
	return select($params);
}

/**
 * returns a select form element for months
 * params array:
 * 'class' = css class, default false
 * 'label' = label name, defaults to 'Month'
 * 'name' = select name, defaults to 'month'
 * 'id' = css id of select element, defaults to false
 */
function monthSelect($params = array()) {
	$param_defaults = array('class' => false,
							'label' => 'Month',
							'name' => 'month',
							'id' => false);
	$params = array_merge($param_defaults, $params);
	$months = array('January',
					'February',
					'March',
					'April',
					'May',
					'June',
					'July',
					'August',
					'September',
					'October',
					'November',
					'December');
	$params['options'] = map(function($month) {
			return array('value' => $month, 'label' => $month);
		}, $months);
	return select($params); 
}

/**
 * returns a select form element for days
 * params array:
 * 'class' = css class, default false
 * 'label' = label name, defaults to 'Day'
 * 'name' = select name, defaults to 'day'
 * 'id' = css id of select element, defaults to false
 */ 
function daySelect($params = array()) {
	$param_defaults = array('class' => false,
							'label' => 'Day',
							'name' => 'day',
							'id' => false);
	$params = array_merge($param_defaults, $params);
	$days = range(1, 31);
	$params['options'] = map(function($day) {
			return array('value' => $day, 'label' => $day);
		}, $days);
	return select($params); 
}

/**
 * returns a select form element for days
 * params array:
 * 'class' = css class, default false
 * 'label' = label name, defaults to 'Hour'
 * 'name' = select name, defaults to 'hour'
 * 'id' = css id of select element, defaults to false
 */ 
function hourSelect($params = array()) {
	$param_defaults = array('class' => false,
							'label' => 'Hour',
							'name' => 'hour',
							'id' => false);
	$params = array_merge($param_defaults, $params);
	$hours = range(0, 23);
	$params['options'] = map(function($hour) {
			return array('value' => $hour, 'label' => $hour);
		}, $hours);
	return select($params); 
}

/**
 * returns a select form element for minutes
 * params array:
 * 'class' = css class, default false
 * 'label' = label name, defaults to 'Minute'
 * 'name' = select name, defaults to 'minute'
 * 'id' = css id of select element, defaults to false
 */ 
function minuteSelect($params = array()) {
	$param_defaults = array('class' => false,
							'label' => 'Minute',
							'name' => 'minute',
							'id' => false);
	$params = array_merge($param_defaults, $params);
	$minutes = range(0, 59);
	$params['options'] = map(function($minute) {
			return array('value' => $minute, 'label' => $minute);
		}, $minutes);
	return select($params); 
}
