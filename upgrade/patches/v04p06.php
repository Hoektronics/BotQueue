<?
include("../../extensions/global.php");
include("../patches.php");

$patchNumber = 6;
start_patch();

if (!patch_exists($patchNumber)) {
	$createTable = "CREATE TABLE `engine_os` (
		`engine_id` INT(11) UNSIGNED NOT NULL,
		`os` ENUM('osx','linux','win','raspberrypi'),
		PRIMARY KEY (`engine_id`, `os`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8";

	db()->execute($createTable);

	finish_patch($patchNumber, "Creating Engine OS table");
}

?>
