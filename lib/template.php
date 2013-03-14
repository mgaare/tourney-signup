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
