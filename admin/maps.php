<?php

require_once('../lib/base.php');

function maps() {
	if (isset($_POST['mode']) && !(empty($_POST['mode']))) {
		maps_process();
	} else { maps_form(); }
}

function maps_form() {
	$mode = new Mode();
	$map = new Map();
	$template = new AdminTemplate();
	$templateSnippet = new TemplateSnippet();
	
	// get all the modes
	$modes = $mode->findSimple();
	
	// get all the maps
	$maps = $map->findSimple();
	
	// for each map, get any modes it is already assigned to
	$mode_maps = map(function($map_info) use ($map) {
		$ret = $map_info;
		$ret['modes'] = $map->getAllModes($map_info);
		return $ret;
	}, $maps);
	
	$view_vars = array('modes' => $modes, 'maps' => $mode_maps);
	$templateSnippet->setTemplateFile('../views/admin/maps.php');
	$content = $templateSnippet->render($view_vars);
	echo $template->render($content);
}

function maps_process() {
	echo 'maps_process';
}

if (Admin::isAdmin()) {
	maps();
} else {
	die(header('Location: index.php'));
}
