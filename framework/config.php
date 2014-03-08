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
	 * @param $key mixed the key to fetch.
	 * @return mixed the value of that key.
	 */
	public static function get($key)
	{
		return self::$data[$key];
	}

	/**
	 * set a global configuration value
	 *
	 * @param $key mixed the key to set.
	 * @param $val mixed the value to set the key to.
	 */
	public function set($key, $val)
	{
		self::$data[$key] = $val;
	}
}