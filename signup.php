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
		log_in_error();
	}
}

function log_in_form() {
	
}

function log_in_error() {
	// bad login
}

function sign_up() {
	// check to see if there's a POST, otherwise show sign up form
}

$user = new User();
if ($user->isLoggedIn()) {
	sign_up();
} else { log_in(); }
