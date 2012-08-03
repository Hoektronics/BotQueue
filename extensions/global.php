<?
	// figure out the base dir, we're two directories past the base dir
	$parts = explode("/", dirname(__FILE__));
	array_pop($parts);
	$base_dir = join('/', $parts);

	include($base_dir . "/framework/global.php");
	include(EXTENSIONS_DIR . "config.php");

	// Decides which include path delimiter to use.  Windows should be using a semi-colon
	// and everything else should be using a colon.  If this isn't working on your system,
	// comment out this if statement and manually set the correct value into $path_delimiter.
	if (strpos(__FILE__, ':') !== false) {
		$path_delimiter = ';';
	} else {
		$path_delimiter = ':';
	}
	
	// This will add the packaged PEAR files into the include path for PHP, allowing you
	// to use them transparently.  This will prefer officially installed PEAR files if you
	// have them.  If you want to prefer the packaged files (there shouldn't be any reason
	// to), swap the two elements around the $path_delimiter variable.  If you don't have
	// the PEAR packages installed, you can leave this like it is and move on.
	ini_set('include_path', ini_get('include_path') . $path_delimiter . CLASSES_DIR . 'PEAR');
	
	//load our db if we need it.
	if (defined('BQ_DB_HOST'))
		db()->selectDb(BQ_DB_NAME);
?>
