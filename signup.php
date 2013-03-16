<?php

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
	$user = new User();
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
	$user = new User();
	if ($user->exists($username)) {
		$message = 'Invalid Password. Need a password reset? <a href="http://swl.me/index.php/contact-us">Contact Us.</a>'; 	
	} else {
		$message = 'No such user.';
	}
	log_in_form($message);	
}

function sign_up() {
	$sign_up = ModelStore::getInstance('Signup');
	$mode = ModelStore::getInstance('Mode');
	$map = ModelStore::getInstance('Map');
	$event = ModelStore::getInstance('Event');
	
	
	
}

$user = new User();
if ($user->isLoggedIn()) {
	sign_up();
} else { log_in(); }