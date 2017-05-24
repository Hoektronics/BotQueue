<?
include("../patches.php");

$patch = new Patch(15);

if (!$patch->exists()) {

	$sql = "ALTER TABLE queues ADD COLUMN `delay` int(11) unsigned NOT NULL DEFAULT 0 AFTER name";

	db()->execute($sql);

	$patch->finish("Added queue delay");
}