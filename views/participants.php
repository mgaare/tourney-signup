<?php
include_protection(__FILE__);
?>

<h1>SWL Participants</h1>
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
		return $ret; 
	}, $signups);
?>