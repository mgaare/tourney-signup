<?php
include_protection(__FILE__);
?>

<h1>SWL Events</h1>
<?php
if (isset($current_event) && !empty($current_event)) {
	echo "<p>Next upcoming tournament is on "
		. date("F j, G:i GMT", $event['time']) . "</p>";
} else {
	echo "<p>No upcoming tournaments</p>";
}
?>

<h2>Events</h2>
<table>
	<tr>
		<th>Event ID</th><th>Date / Time in GMT</th><th>Modes</th><th>Actions</th>
	</tr>
	
<?php

echo mapcat(function($event) {
		$ret = "<tr><td>{$event['id']}</td><td>" 
			. date("F j, G:i", $event['time'])
			. "</td><td>"
			. implode(', ', map(function($mode) {
					return $mode['name'];
				}, $event['modes']))
			. "</td><td>"
			. "<a href='events.php?action=view&id={$event['id']}'>View</a> "
			. "<a href='events.php?action=edit&id={$event['id']}'>Edit</a> "
			. "<a href='events.php?action=delete&id={$event['id']}'>Delete</a> "
			. "</td></tr>";
		return $ret;
	}, $events);
?>
</table>

<h2>Create New Event</h2>
<h3>(Time in GMT)</h3>

<form method="post">
<?php 
echo View\monthSelect();
echo View\daySelect();
echo View\yearSelect();
echo View\hourSelect();
echo View\minuteSelect();

echo mapcat(function($mode) {
	return View\checkbox(array('name' => "modes[{$mode['id']}][{$mode['id']}]",
							   'id' => "checkbox_mode_{$mode['id']}",
							   'label' => $mode['name']));
	}, $modes);
?>
<input type='Submit' value='Submit'>
</form>