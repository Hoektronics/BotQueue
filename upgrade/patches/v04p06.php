<?
include("../patches.php");

$patch = new Patch(6);

if (!$patch->exists()) {
	$createTable = "CREATE TABLE `engine_os` (
		`engine_id` INT(11) UNSIGNED NOT NULL,
		`os` ENUM('osx','linux','win','raspberrypi'),
		PRIMARY KEY (`engine_id`, `os`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8";

	db()->execute($createTable);

	$patch->finish("Creating Engine OS table");
}
