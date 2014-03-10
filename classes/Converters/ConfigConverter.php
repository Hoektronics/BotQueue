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

class ConfigConverter
{

	public static function convertToKeys($defines)
	{
		$letters = "a-zA-Z_\x7f-\xff";
		$spaces = "\\s*";
		$quotes = "(?:\\'|\\\")";
		$quotesWithEmpty = "(?:\\'|\\\"|)";

		$keyRegex = "[" . $letters . "]" . "[0-9" . $letters . "]*";
		$valueRegex = "(?:[\\s\\-_.@0-9" . $letters . "]*)";


		$defineRegex =
			"/" .
			$spaces . "define" . $spaces .
			"\\(" . $spaces .
			$quotes . "(" . $keyRegex . ")" . $quotes .
			$spaces . "," . $spaces .
			$quotesWithEmpty . "(" . $valueRegex . ")" . $quotesWithEmpty .
			$spaces . "\\)" . $spaces . ";" .
			"/";

		$numDefines = 0;
		$keyValue = array();

		do {
			$continueSearching = false;
			if (preg_match($defineRegex, $defines, $matches) === 1) {
				if (count($matches) == 3) {
					$keyValue[$matches[1]] = ConfigConverter::getRawValue($matches[2]);
					$defines = str_replace($matches[0], "", $defines);

					$numDefines++;
					$continueSearching = true;
				}
			}
		} while ($continueSearching);

		if ($numDefines > 0) {
			return $keyValue;
		} else {
			throw new InvalidConfigDefine("This define is not well-formed");
		}
	}

	private static function getRawValue($string)
	{
		if($string == 'false') {
			return false;
		} else if ($string == 'true') {
			return true;
		} else if (ConfigConverter::startsWith($string, "\"") &&
			ConfigConverter::endsWith($string, "\"")) {
			return substr($string, 1, strlen($string) - 2);
		}
		return $string;
	}

	private static function startsWith($haystack, $needle)
	{
		return strpos($haystack, $needle) === 0;
	}

	private static function endsWith($haystack, $needle)
	{
		return substr($haystack, -strlen($needle)) === $needle;
	}
}