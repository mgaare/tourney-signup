<?php

class Admin {

	private static $admin_pass = '';
	
	static function login($pass) {
		if (set_not_empty($_SERVER, 'SWL_ADMIN_PASS')) {
			self::$admin_pass = $_SERVER['SWL_ADMIN_PASS'];
		}
		if ($pass != self::$admin_pass) {
			return false;
		} else {
			$_SESSION['admin'] = true;
			return true;
		}
	}
	static function isAdmin() {
		return (isset($_SESSION['admin']) && $_SESSION['admin']);
	}
	
	static function notAdmin() {
		die(header('Location: index.php')); // should redirect to login
	}
	
}
