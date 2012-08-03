<?
/*
* @ingroup BJHelpers
* @brief The config class is an easy way to store sitewide configurations.
*
* You dont have to resort to GLOBALS, defines, or any of the other hacky,
* kludge type fixes.  Its a singleton based class, with two methods:  get and
* set.  Its ridiculously easy to use and headache free.
*/
class Config
{
	/**
	* we store the config options in an array.
	*/
	private static $data = array();
	
	/**
	* nobody needs to access this...
	*/
	private function __construct()
	{
	}

	/**
	* get a global configuration value.
	*
	* @param $key the key to fetch.
	*
	* @return the value of that key.
	*/
	public static function get($key)
	{
		return self::$data[$key];
	}

	/**
	* set a global configuration value
	*
	* @param $key the key to set.
	* @param $val the value to set the key to.
	*/
	public function set($key, $val)
	{
		self::$data[$key] = $val;
	}
}
?>