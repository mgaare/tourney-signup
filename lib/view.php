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
 * 'name'
 */
function select($params) {
	
}
