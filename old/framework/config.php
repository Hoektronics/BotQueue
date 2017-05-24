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

class Config
{
	/**
	 * we store the config options in an array.
	 */
	private $data;

	public function __construct($array = array())
	{
		$this->data = $array;
	}

	/**
	 * get a global configuration value.
	 *
	 * @param $key mixed the key to fetch.
	 * @throws Exception
	 * @return mixed the value of that key.
	 */
	public function get($key)
	{
		if (isset($this->data[$key]))
			return $this->data[$key];
		throw new Exception("Invalid Configuration key");
	}

	/**
	 * set a global configuration value
	 *
	 * @param $key mixed the key to set.
	 * @param $val mixed the value to set the key to.
	 */
	public function set($key, $val)
	{
		$this->data[$key] = $val;
	}


	public function save($ini_file)
	{
		$ini_data = array();
		foreach ($this->data as $key => $value) {
			$splitPosition = strpos($key, '/');
			if ($splitPosition !== FALSE) {
				$header = substr($key, 0, $splitPosition);
				$sub_key = substr($key, $splitPosition + 1,
					strlen($key) - $splitPosition - 1);

				if (!isset($ini_data[$header]))
					$ini_data[$header] = array();

				$ini_data[$header][$sub_key] = $value;
			} else {
				$ini_data[$key] = $value;
			}
		}

		print($ini_file . "\n");
		self::write_php_ini($ini_data, $ini_file);
	}

	private static function write_php_ini($array, $file)
	{
		$res = array();
		$first_entry = true;
		foreach ($array as $key => $val) {
			if (is_array($val)) {
				if(!$first_entry)
					$res[] = "";
				$res[] = "[$key]";

				foreach ($val as $skey => $sval)
					$res[] = self::getKeyValue($skey, $sval);
			} else {
				$res[] = self::getKeyValue($key, $val);
			}
			$first_entry = false;
		}

		self::safefilerewrite($file, implode("\r\n", $res));
	}

	private static function getKeyValue($key, $value) {
		$result = "$key = ";
		if($value === false)
			$result .= 'false';
		else if($value === true)
			$result .= 'true';
		else if(is_numeric($value))
			$result .= $value;
		else
			$result .= '"' . $value . '"';

		return $result;
	}

	private static function safefilerewrite($fileName, $dataToSave)
	{
		if ($fp = fopen($fileName, 'wb')) {
			$startTime = microtime();
			do {
				$canWrite = flock($fp, LOCK_EX);
				// If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
				if (!$canWrite) usleep(round(rand(0, 100) * 1000));
			} while ((!$canWrite) and ((microtime() - $startTime) < 1000));

			//file was locked so now we can store information
			if ($canWrite) {
				fwrite($fp, $dataToSave);
				flock($fp, LOCK_UN);
			}
			fclose($fp);
		} else {
			print("Could not write config file\n");
		}
	}
}