<?
	class View
	{
		//hold our private data.
		private $controller;
		private $view;
		
		//init and save our controller/view info.
		public function __construct($controller, $view)
		{
			$this->controller = $controller;
			$this->view = $view;
		}
		
		//placeholder functions for any special stuff you gotta do.
		public function preRender() {}
		public function postRender() {}
	
		public function render($data = array())
		{
			//get our data variables into the local scope
			if (!empty($data))
	        	extract((array)$data, EXTR_OVERWRITE);
	
			//Turn on output buffering - no output is sent from the script. Output is instead stored in an internal buffer.
			ob_start();
			
			//include the appropriate {controller}.{view}.php file, e.g. item.newest.php
			//include the appropriate {controller}.{view}.php file, e.g. item.newest.php
			$view_file = VIEWS_DIR . strtolower("{$this->controller}/{$this->view}.php");
			if (file_exists($view_file))
				//include actually echos stuff the the buffer - for example, check htmltemplate.header.php
				//You'll see this file dropping in and out of php code all over the place
				//Anytime you see the <?= tag, this is telling will echo whatever is to the right of this tag
				include($view_file);
			else
				throw new ViewException("The {$this->controller}.{$this->view} page does not exist!");
		
			//Get the buffer contents and turn off buffering
			return ob_get_clean();
		}
	}
?>
