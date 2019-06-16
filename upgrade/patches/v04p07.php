<?php
include("../patches.php");

$patch = new Patch(7);

if (!$patch->exists()) {
	$addWebcamID = "ALTER TABLE bots ADD COLUMN `webcam_image_id` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
	db()->execute($addWebcamID);

	$patch->finish("Adding webcam image to bot temporarily");
}
