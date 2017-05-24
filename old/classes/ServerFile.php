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

// Local File is already taken
class ServerFile {
	private $name;
	private $path;

	public function __construct($name, $file)
	{
		$this->name = $name;
		$this->path = $file;
	}

	public function getName() {
		return $this->name;
	}

	public function getFile() {
		return $this->path;
	}

	public function isKnownType() {
		return preg_match("/\\." . ACCEPTABLE_FILES . "/i", $this->name) === 1;
	}

	public function isZip() {
		return preg_match("/\\.zip/i", $this->name) === 1;
	}

	public static function downloadFromUrl($url) {
		$tempFile = tempnam('/tmp', 'BOTQUEUE-');
		$fileTarget = fopen($tempFile, 'w');
		$headerFile = tmpfile();

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_WRITEHEADER, $headerFile);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_FILE, $fileTarget);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:24.0) Gecko/20100101 Firefox/24.0');
		$return = curl_exec($ch);
		error_log($return);

		if (!curl_errno($ch)) {
			$parsed_url = parse_url($url);
			$realName = basename($parsed_url['path']);

			//check to see if we got a better name here.
			rewind($headerFile);
			$headers = stream_get_contents($headerFile);
			if (preg_match("/Content-Disposition: .*filename=[\"']?([^ \\r\\n]+)[\"']?/", $headers, $matches))
				$realName = basename(trim($matches[1]));

			if (preg_match("/Location: (.*)/", $headers, $matches))
				$realName = basename(trim($matches[1]));

			//format the info for our the caller.
			$file = new ServerFile($realName, $tempFile);

			// Check if the file actually exists since curl wasn't working with our local files
			if (preg_match("/HTTP\\/.\\.. 404/", $headers))
				$file = null;
		} else
			$file = null;


			//clean up.
		curl_close($ch);
		fclose($headerFile);
		fclose($fileTarget);

		return $file;
	}
}