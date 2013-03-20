<?php

// This is, for most intents and purposes, a 'controller'

require_once('./lib/base.php');

function log_in() {
	// show login form, or check if there's a POST
	if (isset($_POST['username']) && isset($_POST['password'])) {
		check_login($_POST['username'], $_POST['password']);
	} else {
		log_in_form();
	}
}

function check_login($username, $password) {
	$user = ModelStore::getInstance('User');
	if ($login = $user->checkLogin($username, $password)) {
		$user->loginUser($login);
		sign_up_form();	
	} else {
		log_in_error($username, $password);
	}
}

function log_in_form($message = '') {
	$content = <<<EOT
<h1>Please Log In with your SWL Account</h1>
<form method="post">
<p>
	<label for="username">Login Name: </label>
	<input type="text" name="username" id="username">
</p>
<p>
	<label for="password">Password: </label>
	<input type="password" name="password" id="password">
</p>
<input type="submit" value="Submit">
</form>

<p>Don't have an account? <a href="http://swl.me/index.php/register-an-account">Register Here</a></p>

EOT;

	if ($message) {
		$content = '<h3 class="error">' . $message . '</h3>' . $content;
	}
	$template = new UserTemplate();
	print $template->render($content);	
}

function log_in_error($username, $password) {
	$user = ModelStore::getInstance('User');
	if ($user->exists($username)) {
		$message = 'Invalid Password. Need a password reset? <a href="http://swl.me/index.php/contact-us">Contact Us.</a>'; 	
	} else {
		$message = 'No such user.';
	}
	log_in_form($message);	
}

function sign_up() {
	if (empty($_POST)) {
		sign_up_form();
	} else {
		sign_up_process();
	}	
}

function sign_up_process() {
	$signup = new Signup();
	$templateSnippet = new TemplateSnippet();
	$template = new UserTemplate();
	$vote = new Vote();

	$current_event = $signup->event->getCurrent();
	$current_user = $signup->user->getLoggedIn();
	
	$filtered_inputs = array_filter_search($_POST['mode'], 'signup', 'on');
	
	// first we handle the signups - deleting the old ones to handle the user
	// unchecking columns
	// we only want the stuff that the user has actually signed up for
	$signup->deleteForUser($current_user, $current_event);
	$signups = map(function($postval) use ($current_event, $current_user, $signup) {
		$params = array('user_id' => $current_user['id'],
					'event_id' => $current_event['id'],
					'mode_id' => $postval['mode_id']);
		if (isset($postval['team'])) {
			$params['team'] = $postval['team'];
		}
		if (!$signup->create($params)) {
			/*error_log('Failed to save signup with params: ' 
				. print_r($params, true));*/
		}
		return $signup->mode->findById($postval['mode_id']);
		}, $filtered_inputs);
	
	// Now the votes - deleting previous votes first
	/*
	$vote->deleteForUserEvent($current_user, $current_event);
	$saveVotes = function($votes, $user, $event, $vote) {
		$params = array('user_id' => $user['id'],
						'event_id' => $event['id']);
		map(function($vote_input) use ($vote, $params){
			$vote->create(array_merge($params,
							array('qualification' => 1, 
								  'mode_id' => $vote_input['mode_id'],
								  'map_id' => $vote_input['qualification'])));
			}, filter(function($val) { return !(empty($val['qualification'])); }, $votes));
		map(function($vote_input) use ($vote, $params){
			$vote->create(array_merge($params,
							array('all_v_all' => 1, 
								  'mode_id' => $vote_input['mode_id'],
								  'map_id' => $vote_input['all_v_all'])));
			}, filter(function($val) { return !(empty($val['all_v_all'])); }, $votes));
	};

	$saveVotes($filtered_inputs, $current_user, $current_event, $vote);
	
	$votes = map(function($postval) use ($vote) {
		return array('mode_id' => $postval['mode_id'],
					 'qualification' => $vote->map->findById($postval['qualification']),
					 'all_v_all' => $vote->map->findById($postval['all_v_all']));
		}, $filtered_inputs); */
	$votes = array();
	
	$view_params = array('signups' => $signups, 'event' => $current_event,
						 'user' => $current_user, 'votes' => $votes);
	
	$templateSnippet->setTemplateFile('signup_success.php');
	$content = $templateSnippet->render($view_params);
	echo $template->render($content);
	
}

function sign_up_form($message = false) {
	// prepare for some of the ugliest code in this whole app
	$signup = new Signup();
	$mode = new Mode();
	$map = new Map();
	$event = new Event();
	$user = new User();
	$templateSnippet = new TemplateSnippet();
	$template = new UserTemplate();
	
	$current_event = $event->getCurrent();
	// gets the modes that are being played in this event, and then attaches
	// the corresponding maps to them - this is perhaps the only redeeming
	// code in the entire function here
	$modes = map(function($mode) use($map) {
					$mode['maps'] = $map->getForMode($mode);
					return $mode;
				 }, $mode->getForEvent($current_event));
	$current_user = $user->getLoggedIn();
	$current_signup = $signup->getForUser($current_user, 
												 $current_event);
	$view_vars = array('event' => $current_event, 'modes' => $modes, 
					   'user' => $current_user, 'signup' => $current_signup);
					   
	$templateSnippet->setTemplateFile('signup.php');
	$content = $templateSnippet->render($view_vars);
	if ($message) {
		$content = "<h2 class='error'>{$message}</h2>" . $content;
	}
	echo $template->render($content);
}

$user = new User();
if ($user->isLoggedIn()) {
	sign_up();
} else { log_in(); }