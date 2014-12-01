<?
include("../../extensions/global.php");
include("../patches.php");

$patchNumber = 7;
start_patch();

if (!patch_exists($patchNumber)) {
	$addWebcamID = "alter table bots add column `webcam_image_id` int(11) unsigned NOT NULL DEFAULT '0'";
	db()->execute($addWebcamID);

	finish_patch($patchNumber, "Adding webcam image to bot temporarily");
}

?>
