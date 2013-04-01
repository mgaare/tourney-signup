<?php

session_start();

$include_protection = true;

date_default_timezone_set('UTC');

require_once('util.php');
require_once('db.php');
require_once('template.php');
require_once('admin.php');
require_once('view.php');