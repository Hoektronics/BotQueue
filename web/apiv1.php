<?
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
  
	include("../extensions/global.php");
	include(EXTENSIONS_DIR . "session.php");

	//are we in the right place?
	if ($_SERVER['HTTP_HOST'] != SITE_HOSTNAME)
		header("Location: http://" . SITE_HOSTNAME . $_SERVER['REQUEST_URI']);
		
	// If page requires SSL, and we're not in SSL mode, 
	// redirect to the SSL version of the page
	if(FORCE_SSL && $_SERVER['SERVER_PORT'] != 443) {
	   header("HTTP/1.1 301 Moved Permanently");
	   header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	   exit();
	}

	Controller::byName('apiv1')->renderView('endpoint');
?>
