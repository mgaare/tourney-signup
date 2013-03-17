<?php

class Admin {

	private static $admin_pass = '###admin_pass###';
	
	function login($pass) {
		if ($pass != $admin_pass) {
			return false;
		} else {
			$_SESSION['admin'] = true;
			return true;
		}
	}
	function isAdmin() {
		return (isset($_SESSION['admin']) && $_SESSION['admin']);
	}
}
