<?php

class Template {
	
	protected $layout;
		
	public function setLayout($layout) {
		$this->layout = $layout;
	}
	
	public function render($content) {
		ob_start();
		require $this->layout;
		return ob_get_clean();
	}
}

class UserTemplate extends Template {
	
	function __construct() {
		$this->setLayout('<?php echo $content; ?>');
	}
	
}

class AdminTemplate extends Template {
	
	function __construct() {
		$this->setLayout('<?php echo $content; ?>');
	}
}

class TemplateSnippet {
	
	protected $template;
	
	public function setTemplate($template) {
		$this->template = $template;
	}
	
	public function render($params) {
		// Set all the params as variables in local function scope
		foreach ($params as $key => $value) {
			$$key = $value;
		}
		// now render and return it
		ob_start();
		require $this->template;
		return ob_get_clean();
	}
}
