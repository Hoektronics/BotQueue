<?
include("../../extensions/global.php");
include("../patches.php");

$patchNumber = 15;
start_patch();

if (!patch_exists($patchNumber)) {

	$sql = "ALTER TABLE queues ADD COLUMN `delay` int(11) unsigned NOT NULL DEFAULT 0 AFTER name";

	db()->execute($sql);

	finish_patch($patchNumber, "Added queue delay");
}