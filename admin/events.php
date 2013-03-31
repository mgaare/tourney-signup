<?php

require_once('../lib/base.php');

function events() {
	if (isset($_GET['action']) && !empty($_GET['action'])) {
		switch ($_GET['action']) {
			case 'view':
				view_event();
				break;
			case 'edit':
				edit_event();
				break;
			case 'delete':
				delete_event();
				break;
			default:
				event_index();
				break;
		}		
	} elseif (isset($_POST['event']) && !empty($_POST['event'])) {
		save_event();
	} else {
		event_index();
	}
}

function event_index() {
	$event = new Event();
	$mode = new Mode();
	$template = new AdminTemplate();
	$templateSnippet = new TemplateSnippet();

	$events = $event->findSimple(false, 
								 array('order' => 
								 	   array('by' => 'time', 
								 	   		 'dir' => 'desc')));
	$events = map(function($event) use ($mode) {
			$event['modes'] = $mode->getForEvent($event);
			return $event;
		}, $event->findSimple(false, 
			array('order' => 
				array('by' => 'time', 
					  'dir' => 'desc'))));
	$modes = $mode->findSimple();
	$current_event = $event->getCurrent();
	$view_vars = array('events' => $events, 
					   'current_event' => $current_event,
					   'modes' => $modes);
	$templateSnippet->setTemplateFile('../views/admin/event.php');
	$content = $templateSnippet->render($view_vars);
	echo $template->render($content);
}

if (Admin::isAdmin()) {
	events();
} else {
	Admin::notAdmin();
}