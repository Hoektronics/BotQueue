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

// figure out the base dir, we're one directory past the base dir
// so just get the relative path, and then find the actual path
$base_dir = dirname(__FILE__) . "/..";
$base_dir = realpath($base_dir);


//define some useful constants.
define('HOME_DIR', $base_dir);
define('WEB_DIR', $base_dir . '/web/');
define('FRAMEWORK_DIR', $base_dir . '/framework/');
define('EXTENSIONS_DIR', $base_dir . '/extensions/');
define('CLASSES_DIR', $base_dir . '/classes/');
define('VIEWS_DIR', $base_dir . '/views/');
define('CONTROLLERS_DIR', $base_dir . '/controllers/');
define('MODELS_DIR', $base_dir . '/models/');

//simply include all our files...
include(FRAMEWORK_DIR . "/model.php");
include(FRAMEWORK_DIR . "view.php");
include(FRAMEWORK_DIR . "controller.php");
include(FRAMEWORK_DIR . "collection.php");
include(FRAMEWORK_DIR . "db.php");
include(FRAMEWORK_DIR . "exceptions.php");
include(FRAMEWORK_DIR . "file.php");
include(FRAMEWORK_DIR . "cachebot.php");

// create our own loader class
class BotQueue_Loader
{
	static public function __autoload($class)
	{
		$fileName = "/" . strtolower($class) . ".php";

		$di = new RecursiveDirectoryIterator(MODELS_DIR);
		foreach (new RecursiveIteratorIterator($di) as $name => $file) {
			if (strcasecmp(substr($name, -strlen($fileName)), $fileName) == 0) {
				include($name);
				return true;
			}
		}

		$di = new RecursiveDirectoryIterator(CLASSES_DIR);
		foreach (new RecursiveIteratorIterator($di) as $name => $file) {
			if (strcasecmp(substr($name, -strlen($fileName)), $fileName) == 0) {
				include($name);
				return true;
			}
		}

		return false;
	}
}

spl_autoload_register(array('BotQueue_Loader', '__autoload'));