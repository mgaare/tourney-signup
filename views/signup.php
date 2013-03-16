<?php
include_protection(__FILE__);
?>
<h1>Sign up for the next SWL Tournament</h1>

<?php

if (empty($event)) {
	echo "<h2>Signups are not yet active for the next SWL event</h2>";
} else {
?>

<p>Taking place <?php echo date("F j, G:i", $event['time']); ?></p>

<form method="post">

<h2>Which modes are you playing in?</h2>

<?php 
$snippet = function(&$val) {
	$ret = "<p><label for='{$val['name']}-checkbox'>{$val['name']}"
		. "</label>"
		. "<input type='checkbox' name=\"mode[{$val['id']}]['signup']\""  
		. " id='" . $val['name'] . "-checkbox'";
	if (isset($val['signup']) && $val['signup']) {
		$ret .= " checked";
	}
	$ret .= ">";
	if ($val['team_mode'] == 1) {
		$ret .= "<p><label for ='{$val['name']}-team'>Team name: "
			. "<input type='text' name=\"mode[{$val['id']}]['team']\""
			. " id='{$val['name']}-team'";
		if (isset($val['team']) && !empty($val['team'])) {
			$ret .= " value='{$val['team']}'";
		}
		$ret .= ">";
	}
	return $ret;
};

echo mapcat($snippet, $modes);
?>

<h2>What Maps Do You Want?</h2>

<?php

$snippet = function(&$mode) {
	$ret = "<p>Map for {$mode['name']} All v All match: "
		 . "<select name=\"mode['{$mode['id']}']['all_v_all']\">"
		 . "<option value='' selected>No Vote</option>";
	$ret .= mapcat($showMap, 
		filter(function($map) {
			return ($map['all_v_all'] == 1);
		}, $mode['maps']));
	$ret .= "</select></p>";
	$ret .= "<p>Map for {$mode['name']} Qualification matches: "
		. "<select name=\"mode['{$mode['id']}']['qualification']\">"
		. "<option value='' selected>No Vote</option>";
	$ret .= mapcat($showMap,
		filter(function($map) {
			return ($map['qualification'] == 1);
		}, $mode['maps']));
	$ret .= "</select></p>";
};

$showMap = function(&$map) {
	return "<option value='{$map['id']}>{$map['name']}</option>";
};

echo mapcat($snippet, $modes);
?>
<input type="Submit" value="Sign Up">
</form>
<?php } ?>