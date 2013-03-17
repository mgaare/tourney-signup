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
<form type="post">
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
	$modes = map(function($mode_val) use($map) {
					return $mode_val['maps'] = $map->getForMode($mode_val);
				 }, $mode->getForEvent($current_event));
	$current_user = $user->getLoggedIn();
	$current_signup = $signup->getCurrentForUser($current_user, 
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