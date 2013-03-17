<?php
include_protection(__FILE__);
?>
<p>Welcome <?php echo $user['nick']; ?></p>
<h1>Thanks for signing up for SWL</h1>
<p>The tournament will be on <?php echo date("F j, G:i", $event['time']); ?></p>
<h2>You signed up for these modes:</h2>
<ul>
<?php
echo mapcat(function($mode) {
	return "<li>{$mode['name']}</li>";
}, $signups);
?>
</ul>
<h2>You voted for these maps:</h2>
<?php
echo mapcat(function($vote) use($signups) {
		$mode = first(array_filter_search($signups, 'id', $vote['mode_id']));
		$ret = "<dl><dt>{$mode['name']}</dt>"
			 . "<dd>Qualification: {$vote['qualification']['name']}</dd>"
			 . "<dd>All vs All: {$vote['all_v_all']['name']}</dd></dl>";
		return $ret;
	}, $votes);
?>
<p>Click to <a href="signup.php">Change Signup/Votes</a></p>