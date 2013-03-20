<?php
include_protection(__FILE__);
?>

<h1>SWL Signup Summary</h1>
<p>For tournament on <?php echo date("F j, G:i", $event['time']); ?></p>

<?php

echo mapcat(function($mode) {
		$ret = "<h2>Mode: {$mode['mode_name']}</h2>"
			 . "<h3>Participants:</h3><ul>";
		$ret .= mapcat(function($user) {
				$ret = "<li>{$user['nick']}";
				if (isset($user['team']) && !(empty($user['team']))) {
					$ret .= " ({$user['team']})";
				}
				$ret .= "</li>";
				return $ret;
			}, $mode['users']);
		$ret .= "</ul>";
		$ret .= "<h3>Map Votes:</h3>"
			 . "<table><tr>"
			 . "<th>Map</th><th>All vs All</th><th>Qualification</th>"
			 . "</tr>";
		$ret .= mapcat(function($map) {
			return "<tr><td>{$map['name']}</td>"
				 . "<td>{$map['count(all_v_all)']}</td>"
				 . "<td>{$map['count(qualification)']}</td></tr>";
			}, $mode['maps']);
		$ret .= "</table>";
		return $ret; 
	}, $signups_and_votes);
?>
