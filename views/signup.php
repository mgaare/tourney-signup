<?php
include_protection(__FILE__);
?>
<p>Welcome <?php echo $user['nick']; ?></p>
<h1>Sign up for the next SWL Tournament</h1>

<?php

if (empty($event)) {
	echo "<h2>Signups are not yet active for the next SWL event</h2>";
} else {
?>

<p>Taking place <?php echo date("F j, at G:i", $event['time']) . ' GMT'; ?></p>

<form method="post">

<h2>Which modes are you playing in?</h2>

<?php 
$snippet = function($val) use($signup) {
	$ret = "<p><input type='hidden' name=\"mode[{$val['id']}][mode_id]\" "
		. "value='{$val['id']}'>"
		. "<label for='{$val['name']}-checkbox'>{$val['name']}"
		. "</label>"
		. "<input type='checkbox' name=\"mode[{$val['id']}][signup]\""  
		. " id='" . $val['name'] . "-checkbox'";
	// check if they previously signed up
	if ($signup) {
		$mode_prev_signup = first(array_filter_search($signup, 'mode_id', $val['id']));
		if (isset($mode_prev_signup['signup']) && $mode_prev_signup['signup']) {
			$ret .= " checked";
		}
	}
	$ret .= ">";
	if ($val['team_mode'] == 1) {
		$ret .= "<p><label for ='{$val['name']}-team'>Team name: "
			. "<input type='text' name=\"mode[{$val['id']}][team]\""
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
<?php
/*
<h2>What Maps Do You Want?</h2>



$showMap = function($map) {
	return "<option value='{$map['id']}'>{$map['name']}</option>";
};

$snippet = function(&$mode) use($showMap) {
	$ret = "<p>Map for {$mode['name']} All v All match: "
		 . "<select name=\"mode[{$mode['id']}][all_v_all]\">"
		 . "<option value='' selected>No Vote</option>";
	$ret .= mapcat($showMap, array_filter_search($mode['maps'], 'all_v_all', 1));
	$ret .= "</select></p>";
	$ret .= "<p>Map for {$mode['name']} Qualification matches: "
		. "<select name=\"mode[{$mode['id']}][qualification]\">"
		. "<option value='' selected>No Vote</option>";
	$ret .= mapcat($showMap, array_filter_search($mode['maps'], 'qualification', 1));
	$ret .= "</select></p>";
	return $ret;
};

echo mapcat($snippet, $modes);
 *  commented for now
 */
?>
<input type="Submit" value="Sign Up">
</form>
<?php } ?>