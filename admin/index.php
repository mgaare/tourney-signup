<?php

require_once('../lib/base.php');

function event_info() {
	$signup = new Signup();
	$vote = new Vote();
	$user = new User();
	$template = new AdminTemplate();
	$templateSnippet = new TemplateSnippet();
	
	$current_event = $signup->event->getCurrent();
	$event_modes = $signup->mode->getForEvent($current_event);
	
	$mode_signups = map(function($mode) use($user, $signup, $current_event) {
			$ret = array('mode_name' => $mode['name'], 'mode_id' => $mode['id']);
			$signups = $signup->findSimple(array('mode_id' => $mode['id'], 
												 'event_id' => $current_event['id']));
			$ret['users'] = map(function($signup) use ($user) {
					$ret = $user->findById($signup['user_id']);
					if (isset($signup['team'])) {
						$ret['team'] = $signup['team'];
					}
					return $ret;
				}, $signups);
			return $ret;
		}, $event_modes);
	
	$signups_and_votes = map(function($mode_signup) use($current_event, $vote) {
			$votes = $vote->getCountsForEventMode($current_event, array('id' => $mode_signup['mode_id']));
			$ret = $mode_signup;
			$ret['maps'] = map(function($mapvote) use ($vote) {
					if ($mapvote['map_id']) {
						$map = $vote->map->findById($mapvote['map_id']);
					} else { $map = array('name' => 'No Vote'); }
					return array_merge($mapvote, $map);
				}, $votes);
			return $ret;
		}, $mode_signups);
	$view_vars = array('event' => $current_event, 
					   'signups_and_votes' => $signups_and_votes, 
					   'modes' => $event_modes);
	$templateSnippet->setTemplateFile('../views/admin/index.php');
	$content = $templateSnippet->render($view_vars);
	echo $template->render($content);
}

function admin_log_in() {
	// show login form, or check if there's a POST
	if (isset($_POST['password'])) {
		admin_check_login($_POST['password']);
	} else {
		admin_log_in_form();
	}
}

function admin_check_login($password) {
	if (Admin::login($password)) {
		event_info();
	} else {
		log_in_form('Nope, wrong password!');
	}
}

function admin_log_in_form($message = '') {
	$content = <<<EOT
<h1>Admin Login</h1>
<form method="post">
<p>
	<label for="password">Password: </label>
	<input type="password" name="password" id="password">
</p>
<input type="submit" value="Submit">
</form>
EOT;

	if ($message) {
		$content = '<h3 class="error">' . $message . '</h3>' . $content;
	}
	$template = new AdminTemplate();
	print $template->render($content);	
}

if (Admin::isAdmin()) {
	event_info();
} else {
	admin_log_in();
}