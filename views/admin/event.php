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
		<th>Event ID</th><th>Date / Time in GMT</th><th>Actions</th>
	</tr>
	
<?php

echo mapcat(function($event) {
		$ret = "<tr><td>{$event['id']}</td><td>" 
			. date("F j, G:i", $event['time'])
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

<form method="post">
	<label for="month">Month</label>
	<select name="month" id="month">
		<?php
			$months = array('January',
							'February',
							'March',
							'April',
							'May',
							'June',
							'July',
							'August',
							'Septempber', 
							'October',
							'November',
							'December');
			echo mapcat(function($month) {
					return "<option value={$month}>{$month}</option>";
				}, $months);
		?>
	</select>
	<label for="day">Day</label>
	<select name="day" id="day">
		<?php
			echo mapcat(function($day) {
				return "<option value={$day}>{$day}</option>";
			}, range(1, 31));
		?>
	</select>
	<label for="year">Year</label>
	<select name="year" id="year">
		<?php
			echo mapcat(function($year) {
				return "<option value={$year}>{$year}</option>";
			}, range(date('Y') - 1, date('Y') +1));
		?>
	</select>
</form>
