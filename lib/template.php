<?php

class Template {
	// needs to be a function
	protected $layout;
		
	public function setLayout($layout) {
		$this->layout = $layout;
	}
	
	public function render($content) {
		// if we are including any files here, they can check for the
		// include_protection variable to make sure they're not being
		// hit directly from a browser... in other words a kind of stupid hack
		// to let us put our template files inside the webroot			
		$include_protection = true;
		ob_start();
		$layoutfn = $this->layout;
		echo $layoutfn($content);
		return ob_get_clean();
	}
}

class UserTemplate extends Template {
	
	function __construct() {
		$this->setLayout(function ($content) { return "
		<html><head><style>
		h1 { color: orange; }
		h2 { color: orange; }
		#fields {
			width: 200px;
			height: 25px;
			border: 1px solid #CCC;
			margin-left: 10px;
			color: #999999;			
		}
		body {
		 	background-color: #000000; 
			color: #ffffff;
		}
		</style></head>
		<body>
		{$content}
		</body></html>";});
	}
	
}

class AdminTemplate extends Template {
	
	function __construct() {
		$this->setLayout(function ($content) { return "
		<html><head><title>SWL Signup Admin</title></head>
		<body>
		<div class='menu'><a href='index.php'>Admin Home</a> | <a href='maps.php'>Maps</a></div>
		{$content}
		</body></html>"; });
	}
}

class TemplateSnippet {
	
	protected $template;
	
	public function setTemplate($template) {
		$this->template = $template;
	}
	
	public function setTemplateFile($file) {
		$filename = dirname(__FILE__) . "/../views/{$file}";
		if (!file_exists($filename)) {
			error_log("Tried to include template file {$filename} but it doesn't exist");
			return false;
		}		
		$this->template = $filename;
	}
	
	public function render($params) {
		// Set all the params as variables in local function scope
		foreach ($params as $key => $value) {
			$$key = $value;
		}
		// if we are including any files here, they can check for the
		// include_protection variable to make sure they're not being
		// hit directly from a browser... in other words a kind of stupid hack
		// to let us put our template files inside the webroot
		$include_protection = true;
		// now render and return it
		ob_start();
		require $this->template;
		return ob_get_clean();
	}
}
