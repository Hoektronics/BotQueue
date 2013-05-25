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
  
  try
  {
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

  	//get our stuff.
  	$mode = $_GET['mode'];
  	$controller = $_GET['controller'];
  	$view = $_GET['view'];

  	//figure out what mode we're looking at.
  	if (!$mode)
  	{
  	//  //do we have a cookie based mode?
    //  if ($_COOKIE['viewmode'])
    //  {
    //    switch ($_COOKIE['viewmode'])
    //    {
    //      case 'iphone':
    //        $mode = 'iphone';
    //        break;
    //      
    //      case 'html':
    //      default:
    //        $mode = 'html';
    //        break;
    //    }
    //  }
    //  else
    //  {
    //    //figure out if we're iphone or not.
    //    if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'iPod') !== false)
    //      $mode = 'iphone';
    //    else
    //      $mode = 'html';
    //      
    //    //save it for later!
    //    setcookie('viewmode', $mode, time()+60*60*24*30, '/');
    //  }
      $mode = 'html';
    }


  	//what controller to show?
  	//If the user hasn't supplied a controller (e.g. item), then use either the iphone or main controllers (which display the home page)
  	if (!$controller)
  	{
      // if ($mode == 'iphone')
      //  $controller = 'iphone';
      // else
  		$controller = 'main';
  	}
  	if (!$view)
  		$view = 'home';

  	//load the content.
  	//Create a new object of a subclass of the controller class
  	//$main is an object derived from the Controller class, e.g. iphoneController, mainController, itemController, etc.
  	$main = Controller::byName($controller);

  	//call the renderView function (within the Controller class), passing it only the name of the view (e.g. newest)
  	//The renderView function does the following:
  	//  - Sets the the property, $main->data['items'], equal to an array containing one page worth of object (e.g item) data read in from MySQL
  	//  - Includes the appropriate view file {controller}.{view}.php file, e.g. item.newest.php
  	//  - Returns the output of the Controller->renderview (note: this is not displayed to the screen, rather it's stored in the $content variable)
  	$content = $main->renderView($view);

  	//now draw it in our template.
  	//Does the following:
  	//  - Create a new instance of a controller, e.g. htmltemplatecontroller.php (see controllers/htmltemplate.php)
  	//  - Call the renderView function on that new controller object, telling it to render the 'main' view with the arguments: content, title and sidebar
  	//  - Echo all that shizzle to the screen
  	//Note that $mode often is not defined
  	echo Controller::byName("{$mode}Template")->renderView('main', array(
  		'content' => $content,
  		'title' => $main->get('title'),
  		'area' => $main->get('area'),
  		'sidebar' => $main->get('sidebar')
  	)); 
  }
  catch (Exception $ex)
  {
    echo "Something bad happened: " . $ex->getMessage();
  }
?>