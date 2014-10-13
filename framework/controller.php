<?php

/*
  This file is part of BotQueue.

  BotQueue is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  BotQueue is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with BotQueue.  If not, see <http://www.gnu.org/licenses/>.
*/

class Controller
{
	private $view_name;
	private $controller_name;
	private $args;
	private $data;

	public static $scriptContents = array();
	public static $scriptTypes = array();
	public static $scripts = array();
	public static $rssFeeds = array();

	public function __construct($name)
	{
		$this->controller_name = $name;
	}

	public function setTitle($title)
	{
		$this->set('title', $title);
	}

	public function addTemplate($name, $content) {
		$this->addScript($name, $content, "text/template");
	}
	public function addScript($name, $content = NULL, $type = "text/template") {
		if($content == NULL) {
			self::$scripts[] = $name;
		} else {
			self::$scriptTypes[$name] = $type;
			self::$scriptContents[$name] = $content;
		}
	}

	public function addRssFeed($title, $url)
	{
		self::$rssFeeds[] = array(
			'title' => $title,
			'url' => $url
		);
	}

	/**
	 * @param $name string
	 * @return Controller
	 * @throws ViewException
	 */
	public static function byName($name)
	{
		// Get the name of the class to load
		$class_name = "{$name}Controller";
		$class_file = CONTROLLERS_DIR . "/" . strtolower($name) . ".php";

		if (file_exists($class_file))
			require_once($class_file);
		else
			throw new ViewException("$name controller does not exist.");

		//returns a new instance of an object which is derived from the Controller class
		//e.g. returns the iphoneController or itemController objects
		return new $class_name($name);
	}

	public function assertLoggedIn()
	{
		if (!User::isLoggedIn()) {
			//create our payload
			$payload = array(
				'type' => 'redirect',
				'data' => $_SERVER['REQUEST_URI']
			);
			$payloadEncoded = base64_encode(serialize($payload));

			$this->forwardToUrl("/login/{$payloadEncoded}");
		}
	}

	public function assertAdmin()
	{
		$this->assertLoggedIn();

		if (!User::isAdmin())
			die("You must be an admin to enter.");
	}


	public function viewFactory()
	{
		//Note: this function is somewhat unused - $mode is not defined anywhere in this class, so $class always defaults to "View"
		//Zach wrote this for additional functionality in the future but it is not currently being used
		//$class = ucfirst($mode) . "View";
		$class = "View";

		//The "View" class does exist (see /framework/view.php) so the function returns a new instance of this class
		if (class_exists($class))
			return new $class($this->controller_name, $this->view_name);
		else
			die("Cannot display the view page");
	}

	public function renderTemplate($template_name) {
		$template = new Template($this->controller_name, $template_name);
		return $template->render();
	}

	public function renderView($view_name, $args = array())
	{
		//save our params, prep for drawing the view.
		if (!empty($args))
			$this->args = $args;
		else
			//if no additional arguments were passed in (besides view) then call getArgs function to get the parameters (e.g. via POST, GET, etc.)
			//for example, args may be: controller=item&view=newest&page=$2
			$this->args = $this->getArgs();

		//call our controller's view method
		//e.g. check to see if the 'newest' method exists within the itemController object
		//if the specified view method exists within this object, then call it (e.g. 'newest' in ItemController)
		//If the method doesn't exist, but the call function does, we should try it anyway
		if (method_exists($this, $view_name) || method_exists($this, "__call"))
			$this->$view_name();

		//Set the view_name property of this object to the appropriate view name (e.g. draw_log_entries)
		$this->view_name = $view_name;

		//$this->viewFactory returns a new object of the type View class, setting the appropriate controller and view properties
		/* @var $view View */
		$view = $this->viewFactory();

		//preRender doesn't do anything - its just a placeholder
		$view->preRender();

		//The $view->render function returns the output of the view {controller}.{view}.php file, e.g. htmltemplate.header.php
		//The function returns the error handling output (if any)
		$output = $view->render($this->data);

		//postRender doesn't do anything - its just a placeholder
		$view->postRender();

		//Returns the contents of the output buffer
		return $output;
	}

	public function get($key = null)
	{
		if ($key === null)
			return $this->data;
		else
			return $this->data[$key];
	}

	public function set($key, $data)
	{
		$this->data[$key] = $data;
	}

	public function args($key = null)
	{
		if ($key === null)
			return $this->args;
		else
			return $this->args[$key];
	}

	protected function setArg($key)
	{
		$this->set($key, $this->args[$key]);
	}

	protected function setView($view_name)
	{
		$this->view_name = $view_name;
	}

	protected function forwardToURL($url)
	{
		header("Location: {$url}");
		exit();
	}

	private function getArgs()
	{
		//use our already set args.
		$args = array();

		// GET is the first level of args.
		if (count($_GET))
			$args = array_merge($args, $_GET);

		// POST overrides GET.
		if (count($_POST))
			$args = array_merge($args, $_POST);

		// JSON data overrides GET and POST
		if (!empty($args['jdata'])) {
			$json_data = json_decode(stripslashes($args['jdata']), true);
			unset($args['jdata']);
			$args = array_merge($args, $json_data);
		}

		// user-defined args rule all!
		if (count($this->args))
			$args = array_merge($args, $this->args);

		return $args;
	}
}