<?
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