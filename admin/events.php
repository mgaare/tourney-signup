<?php

require_once('../lib/base.php');

function events() {
	if (set_not_empty($_GET, 'action')) {
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
	} elseif (isset($_POST) && !empty($_POST)) {
		save_event();
	} else {
		event_index();
	}
}

function save_event() {
	if (set_not_empty($_POST, 'id')) {
		update_event();
	} else {
		create_event();
	}
}

function create_event() {
	// first, create an array with the keys from $_POST we want, with 
	// values of false
	$post_keys = array_fill_keys(
		array('year', 'month', 'day', 'hour', 'minute'), 
		false);
	// then we extract all the matching $_POST keys, with default values 
	// of false	
	$post_vars = array_merge($post_keys, 
		array_intersect_key($_POST, $post_keys));
	// now we see if we need to return an error for missing data
	if (in_array(false, $post_vars)) {
		// if so, we generate the message based on the missing keys
		// filter for false values, then flip array so the values are now
		// the key names
		$missing = array_flip(filter(function($val) {
				 return $val === false; 
			}, $post_vars));
		// redirect to event index with error message indicating missing keys
		event_index('Missing information for Event: ' . mapcat(function($val) {
				return ucfirst($val) . ' ';
			}, $missing));
	} else {
		// for convenience so I'm not constantly typing $_POST, get the time
		// data I want set as local variables
		extract($post_vars);
		$time = strtotime("{$month} {$day}, {$year} {$hour}:{$minute}");
		$event = new Event();
		// save the event
		if (!$current_event = $event->create(array('time' => $time))) {
			event_index('Failed to save the event');
		} else {
			// save the modes
			if (set_not_empty($_POST, 'modes')) {
				$mode = new Mode();
				$modes = map(function($mode_id) {
						return array('mode_id' => $mode_id);
					}, $_POST['modes']);
				$mode->saveAllForEvent($current_event, $modes);
			}
			// redirect to event view
			view_event($current_event['id']);
		}
	}
}

function view_event($id = false) {
	$event = new Event();
	$mode = new Mode();
	$signup = new Signup();
	$template = new AdminTemplate();
	$templateSnippet = new TemplateSnippet();

	if ($id) {
		$current_event = $event->findById($id);
	} elseif (set_not_empty($_GET, 'id')) {
		$current_event = $event->findById($_GET['id']);
	} else {
		$current_event = $event->getCurrent();
	}

	$event_modes = $mode->getForEvent($current_event);
	$e_m_with_signups = map(function($mode) use ($signup, $current_event) {
			// get the signups for the event and mode
			$signups = $signup->findSimple(array(
					'event_id' => $current_event['id'],
					'mode_id' => $mode['id']),
				array('order' => array(
					'by' => 'team',
					'dir' => 'asc')));
			// append the user data to each signup
			$signups_with_users = map(function($signup_data) use ($signup) {
					$signup_data['User'] = $signup->user->findById($signup_data['user_id']);
					return $signup_data;
				}, $signups);
			$mode['signups'] = $signups_with_users;
			return $mode;
		}, $event_modes);
	$view_vars = array('event' => $current_event,
					   'modes' => $event_modes,
					   'signups' => $e_m_with_signups);
	$templateSnippet->setTemplateFile('../views/admin/view_event.php');
	$content = $templateSnippet->render($view_vars);
	echo $template->render($content);
}

function event_index($message = false) {
	$event = new Event();
	$mode = new Mode();
	$template = new AdminTemplate();
	$templateSnippet = new TemplateSnippet();

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
	if ($message) {
		$content = $message . $content;
	}
	echo $template->render($content);
}

if (Admin::isAdmin()) {
	events();
} else {
	Admin::notAdmin();
}