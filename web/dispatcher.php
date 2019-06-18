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

define("START_TIME", microtime(true));

try {
	include("../extensions/global.php");
	include(EXTENSIONS_DIR . "session.php");
	if (defined('SENTRY_DSN')) {
		Sentry\init(['dsn' => SENTRY_DSN ]);
	}

	//are we in the right place?
	if ($_SERVER['HTTP_HOST'] != SITE_HOSTNAME) {
		header("Location: http://" . SITE_HOSTNAME . $_SERVER['REQUEST_URI']);
		exit();
	}

	// If page requires SSL, and we're not in SSL mode,
	// redirect to the SSL version of the page
	if (FORCE_SSL && $_SERVER['SERVER_PORT'] != 443) {
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		exit();
	}

	// What mode are we looking at, ajax or html. Default is html
    if(array_key_exists('mode', $_GET)) {
        $mode = $_GET['mode'];
    } else {
        $mode = 'html';
    }
	$controller = $_GET['controller'];
	$view = $_GET['view'];

	// The main controller is the default
	if (!$controller)
		$controller = 'main';

	if (!$view)
		throw new Exception("No view specified");

	// load the content
	$main = Controller::byName($controller);

	// render the views
	$content = $main->renderView($view);

	// add in headers and footers for html, or nothing for ajax
	echo Controller::byName("{$mode}Template")->renderView('main', array(
		'content' => $content,
		'title' => $main->get('title'),
		'area' => $main->get('area')
	));
} catch (Exception $ex) {
	echo "Something bad happened: " . $ex->getMessage();
    Sentry\captureException($ex);
}
