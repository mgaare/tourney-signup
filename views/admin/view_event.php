<?php
include_protection(__FILE__);
?>

<h1>SWL Event</h1>
<?php

echo "<p>Date and time of event: " . date("F j, G:i GMT", $event['time']) 
	. "</p>";
?>

<h2>Modes and Signups</h2>
<?php

echo mapcat(function($mode) {
		$ret = "<h3>{$mode['name']}</h3>";
		if (count($mode['signups']) > 0) {
			$ret .= "<table><tr><th>User ID</th><th>User Name</th>";
			if ($mode['team_mode'] == 1) {
				$ret .= "<th>Team Name</th>";
			}
			$ret .= "</tr>";
			$ret .= mapcat(function($signup) {
					$s_ret = "<tr><td>{$signup['User']['id']}</td>"
						. "<td>{$signup['User']['nick']}</td>";
					if (!empty($signup['team'])) {
						$s_ret .= "<td>{$signup['team']}</td>";
					}
					$s_ret .= "</tr>";
					return $s_ret;
				}, $mode['signups']);
			$ret .= "</table>";
		} else {
			$ret .= "<p>No signups for this mode</p>";
		}
		return $ret;
	}, $signups);
?>

<?php
//debug
echo '<pre>';

echo 'modes: ';

print_r($modes);

echo 'signups: ';
print_r($signups);

echo '</pre>';