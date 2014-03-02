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
    // Not currently used, but will be in the future
    // private $mode;
    private $args;
    private $data;

    public static $rssFeeds = array();
    public static $content_for = array(
        'head' => '',
        'body' => '',
        'footer' => ''
    );

    public function __construct($name)
    {
        $this->controller_name = $name;
    }

    public static function makeControllerViewKey($controller_name, $view_name, $params)
    {
        return sha1("{$controller_name}.{$view_name}." . serialize($params));
    }

    public static function isiPhone()
    {
        return (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'iPod') !== false);
    }

    public function setTitle($title)
    {
        $this->set('title', $title);
    }

    public function getBrowserType()
    {
        return $_COOKIE['viewmode'];
    }

    public function setSidebar($sidebar)
    {
        $this->set('sidebar', $sidebar);
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


    public function renderView($view_name, $args = array(), $cache_time = 0, $key = null)
    {
        // Check the cache
        /*
        if ($cache_time > Cache::TIME_NEVER)
        {
            if ($key === null)
                $key = Controller::makeControllerViewKey($this->controller_name, $view_name, $args);

            $data = CacheBot::get($key);

            if ($data !== false)
                return $data;
        }
        */

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
        //This method sets the the property, $this->data['items'], equal to an array containing one page worth of object (e.g item) data read in from MySQL
        if (method_exists($this, $view_name))
            $this->$view_name();

        //no cache, get down to business
        //Set the view_name property of this object to the appropriate view name (e.g. draw_log_entries)
        $this->view_name = $view_name;

        //$this->viewFactory returns a new object of the type View class, setting the appropriate controller and view properties
        /* @var $view View */
        $view = $this->viewFactory();

        //do our dirty work.

        //preRender doesn't do anything - its just a placeholder
        $view->preRender();

        //The $view->render function returns the output of the view {controller}.{view}.php file, e.g. htmltemplate.header.php
        //The function returns the error handling output (if any)
        $output = $view->render($this->data);

        //postRender doesn't do anything - its just a placeholder
        $view->postRender();

        //do we save it to cache?
        /*
        if ($cache_time > Cache::TIME_NEVER)
            CacheBot::set($output, $key, $cache_time);
        */

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
        //for ease of debug.
        ob_start();

        //use our already set args.
        $args = array();

        // GET is the first level of args.
        if (count($_GET))
            $args = array_merge($args, $_GET);
        echo "After GET:\n";
        print_r($args);

        // POST overrides GET.
        if (count($_POST))
            $args = array_merge($args, $_POST);
        echo "After POST:\n";
        print_r($args);

        // JSON data overrides GET and POST
        if (!empty($args['jdata'])) {
            $json_data = json_decode(stripslashes($args['jdata']), true);
            unset($args['jdata']);
            $args = array_merge($args, $json_data);
        }
        echo "After jdata:\n";
        print_r($args);

        // user-defined args rule all!
        if (count($this->args))
            $args = array_merge($args, $this->args);
        echo "Finally:\n";
        print_r($args);

        //for debug;
        ob_end_clean();

        return $args;
    }
}

?>
