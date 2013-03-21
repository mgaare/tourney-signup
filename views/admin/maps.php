<?php
include_protection(__FILE__);
?>

<h1>Select Which Maps are Active for Each Mode</h1>
<form method="post">

<?php

function mapCheckbox($map, $mode, $type) {
	$ret = "<input type='checkbox' id='{$mode['name']}-map{$map['id']}-{$type}'
			name='mode[{$mode['id']}][maps][{$map['id']}][{$type}]'";
	if (isset($map['modes']) && $map['modes']) {
		$mode_check = first(array_filter_search($map['modes'], 'mode_id', $mode['id']));
		if ($mode_check[$type] == 1) {
			$ret .= ' checked ';
		}
	}
	$ret .= ">";
	return $ret;
};

function displayMap($mode) {
	return function($map) use ($mode) {
		$ret = "<tr><td>
			<input type='hidden' name='mode[{$mode['id']}][maps][{$map['id']}][id]' value='{$map['id']}'>
			{$map['name']}</td>
		<td>" . mapCheckbox($map, $mode, 'all_v_all')
		. "</td><td>" . mapCheckbox($map, $mode, 'qualification')
		. "</td></tr>";
		return $ret;
	};
};

function displayModeMaps($mode) {
	return function($maps) use ($mode) {
		$ret = "<h2>Select Maps for {$mode['name']}</h2>
			<table><tr>
				<th>Name</th>
				<th>All vs. All</th>
				<th>Qualification</th>
			</tr>";
		$mapFn = displayMap($mode);
		$ret .= mapcat($mapFn, $maps);
		$ret .= "</table>";
		return $ret;
	};
};

// we need to set up some hidden form fields
echo mapcat(function($mode) {
	return "<input type='hidden' name='mode[{$mode['id']}][id]' "
			. "value='{$mode['id']}'>\n";
}, $modes);

// show the maps for each mode
echo mapcat(function($mode) use ($maps) {
	$displayFn = displayModeMaps($mode);
	return $displayFn($maps);
}, $modes);

?>
<input type="submit" value="Submit">
</form>
