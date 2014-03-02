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

	class File
	{
	    var $tempFolder;
	    var $tempFiles = array();

	    function __destruct () {
	        foreach ($this->tempFiles as $file) {
	            unlink($file['temp']);
	        }
	    }
    
	    function __construct ($temp)
	    {
	        $this->tempFolder = $temp;
	    }
    
		function open($url) {
			return fopen($this->get($url), 'r');
		}

	    function get ($url) {
	        array_unshift($this->tempFiles, array(
	            'extension'=> array_pop(explode('.', $url)),
	            'original'=> basename($url),
	            'temp'=> $this->tempFolder . md5(microtime()),
	        ));
	        $ch = curl_init($url);
	        $fp = fopen($this->tempFiles[0]['temp'], 'w');
	        curl_setopt($ch, CURLOPT_FILE, $fp);
	        curl_setopt($ch, CURLOPT_HEADER, 0);
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	        curl_exec($ch);
	        curl_close($ch);
	        fclose($fp);
	        return $this->tempFiles[0]['temp'];
	    }
    
	    function read ($index = 0) {
	        return file_get_contents($this->tempFiles[$index]['temp']);
	    }
    
	    function readArray ($index = 0)
	    {
	        return file($this->tempFiles[$index]['temp']);
	    }
    
	    function listFiles () {
	        return $this->tempFiles;
	    }
    
	    function save ($path, $index = 0) {
	        copy($this->tempFiles[$index]['temp'], (is_dir($path) ? $path . $this->tempFiles[$index]['original'] : $path));
	    }
	}
?>
