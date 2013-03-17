<?php
include_protection(__FILE__);
?>

<h1>SWL Signup Summary</h1>
<p>For tournament on <?php echo date("F j, G:i", $event['time']); ?></p>

<?php
$votes_and_signups = map(function($signup) use ($votes) {
		// Since we have the votes and the signups separate (I'm sure I did this
		// for a reason in the controller but cannot reacll it right now)
		// it will be easier to work with if we merge them
		return array_merge($signup, array_filter_search($votes, 'mode_id', $signup['mode_id'])); 
	}, $signups);
	
echo mapcat(function($mode) {
		$ret = "<h2>Mode: {$mode['mode_name']}</h2>"
			 . "<h3>Participants:</h3><ul>";
		$ret .= mapcat(function($user) {
				return "<li>{$user['nick']}</li>";
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
		return $ret; 
	}, $votes_and_signups);
?>
