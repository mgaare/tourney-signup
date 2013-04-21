<?php

require_once('./lib/base.php');

function event_info() {
	$signup = new Signup();
	$user = new User();
	$mode = new Mode();
	$template = new UserTemplate();
	$templateSnippet = new TemplateSnippet();
	
	$current_event = $signup->event->getCurrent();
	$event_modes = $signup->mode->getForEvent($current_event);
	
	$mode_signups = map(function($mode) use($user, $signup, $current_event) {
			$ret = $mode;
			$signups = $signup->findSimple(array('mode_id' => $mode['id'], 
												 'event_id' => $current_event['id']),
											array('order' => array('by' => 'team')));
			$ret['users'] = map(function($signup) use ($user) {
					$ret = $user->findById($signup['user_id']);
					if (isset($signup['team'])) {
						$ret['team'] = $signup['team'];
					}
					return $ret;
				}, $signups);
			return $ret;
		}, $event_modes);
	
	$view_vars = array('event' => $current_event, 
					   'signups' => $mode_signups, 
					   'modes' => $event_modes);
	$templateSnippet->setTemplateFile('../views/participants.php');
	$content = $templateSnippet->render($view_vars);
	echo $template->render($content);
}

event_info();
