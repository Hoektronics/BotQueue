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

	public static function convertDefines($defines)
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

	public static function convertKeys($keys) {
		$result = array();

		if(isset($keys["FORCE_SSL"]))
			$result["force_ssl"] = $keys["FORCE_SSL"];
		if(isset($keys["COMPANY_NAME"]))
			$result["company_name"] = $keys["COMPANY_NAME"];
		if(isset($keys["IS_DEV_SITE"]))
			$result["dev_site"] = $keys["IS_DEV_SITE"];
		if(isset($keys["SITE_HOSTNAME"]))
			$result["hostname"] = $keys["SITE_HOSTNAME"];
		if(isset($keys["RR_PROJECT_NAME"]))
			$result["db/name"] = $keys["RR_PROJECT_NAME"];
		if(isset($keys["RR_DB_HOST"]))
			$result["db/host"] = $keys["RR_DB_HOST"];
		if(isset($keys["RR_DB_PORT"]))
			$result["db/port"] = $keys["RR_DB_PORT"];
		if(isset($keys["RR_DB_USER"]))
			$result["db/user"] = $keys["RR_DB_USER"];
		if(isset($keys["RR_DB_PASS"]))
			$result["db/pass"] = $keys["RR_DB_PASS"];
		if(isset($keys["AMAZON_AWS_KEY"]))
			$result["aws/key"] = $keys["AMAZON_AWS_KEY"];
		if(isset($keys["AMAZON_AWS_SECRET"]))
			$result["aws/secret"] = $keys["AMAZON_AWS_SECRET"];
		if(isset($keys["EMAIL_METHOD"]))
			$result["email/method"] = $keys["EMAIL_METHOD"];
		if(isset($keys["SES_USE_DKIM"]))
			$result["email/ses_dkim"] = $keys["SES_USE_DKIM"];
		if(isset($keys["EMAIL_USERNAME"]))
			$result["email/user"] = $keys["EMAIL_USERNAME"];
		if(isset($keys["EMAIL_NAME"]))
			$result["email/name"] = $keys["EMAIL_NAME"];
		if(isset($keys["EMAIL_PASSWORD"]))
			$result["email/pass"] = $keys["EMAIL_PASSWORD"];
		if(isset($keys["EMAIL_SMTP_SERVER"]))
			$result["email/smtp_server"] = $keys["EMAIL_SMTP_SERVER"];
		if(isset($keys["EMAIL_SMTP_SERVER_PORT"]))
			$result["email/smtp_port"] = $keys["EMAIL_SMTP_SERVER_PORT"];
		if(isset($keys["TRACK_SQL_QUERIES"]))
			$result["track/sql"] = $keys["TRACK_SQL_QUERIES"];
		if(isset($keys["TRACK_CACHE_HITS"]))
			$result["track/cache"] = $keys["TRACK_CACHE_HITS"];
		if(isset($keys["THINGIVERSE_API_CLIENT_ID"]))
			$result["thingiverse/client_id"] = $keys["THINGIVERSE_API_CLIENT_ID"];
		if(isset($keys["THINGIVERSE_API_CLIENT_SECRET"]))
			$result["thingiverse/client_secret"] = $keys["THINGIVERSE_API_CLIENT_SECRET"];

		return $result;
	}
}