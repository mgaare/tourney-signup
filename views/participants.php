<?php
include_protection(__FILE__);
?>

<h1>SWL Participants</h1>
<p>For tournament on <?php 
echo date("F j, G:i", 
		  View\localTimeZone($event['time'])); 
		  
?>, German Time</p>

<?php

function individualModeDisplay($mode) {
	$ret = "<ul>"
		. mapcat(function($user) {
			return "<li>{$user['nick']}</li>";
		}, $mode['users'])
		. "</ul>";
	return $ret;
};

function teamModeDisplay($mode) {
	$users = group_by($mode['users'], 'team');
	$list = array();
	foreach ($users as $team => $members) {
		$list[] = "{$team} (" . implode(", ", 
										map(function($member) {
											return $member['nick'];
										}, $members))
							  . ")"; 
	}
	$ret = "<ul>"
		. mapcat(function($team) { return "<li>{$team}</li>"; }, $list)
		. "</ul>";
	return $ret; 
};

echo mapcat(function($mode) {
		$ret = "<h2>Mode: {$mode['name']}</h2>";
		if ($mode['team_mode'] == 1) {
			$ret .= teamModeDisplay($mode);
		}
		else {
			$ret .= individualModeDisplay($mode);
		}
		return $ret; 
	}, $signups);
?>