<?php

require_once('../lib/base.php');

function events() {
	
}

if (Admin::isAdmin()) {
	events();
} else {
	Admin::notAdmin();
}