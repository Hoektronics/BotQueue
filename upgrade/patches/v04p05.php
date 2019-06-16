<?php
include("../patches.php");

$patch = new Patch(5);

if (!$patch->exists()) {
	$addContentIDKey = "ALTER TABLE comments ADD KEY `content_id` (`content_id`)";
	db()->execute($addContentIDKey);

	$addContentTypeKey = "ALTER TABLE comments ADD KEY `content_type` (`content_type`)";
	db()->execute($addContentTypeKey);

	$addWebcamID = "ALTER TABLE jobs ADD COLUMN `webcam_image_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER verified_time;";
	db()->execute($addWebcamID);

	$addWebcamImages = "ALTER TABLE jobs ADD COLUMN `webcam_images` TEXT NOT NULL AFTER webcam_image_id";
	db()->execute($addWebcamImages);

	$dropTimestamp = "ALTER TABLE oauth_consumer_nonce DROP INDEX timestamp";
	db()->execute($dropTimestamp);

	$dropNonce = "ALTER TABLE oauth_consumer_nonce DROP INDEX nonce";
	db()->execute($dropNonce);

	$addIPAddressKey = "ALTER TABLE oauth_token ADD KEY `ip_address` (`ip_address`)";
	db()->execute($addIPAddressKey);

	$addParentIDKey = "ALTER TABLE s3_files ADD KEY `parent_id` (`parent_id`)";
	db()->execute($addParentIDKey);

	$modifyThingiverseToken = "ALTER TABLE users MODIFY `thingiverse_token` VARCHAR(40) NOT NULL DEFAULT ''";
	db()->execute($modifyThingiverseToken);

	$modifyThumbnail = "ALTER TABLE users MODIFY `dashboard_style` ENUM('list','large_thumbnails','medium_thumbnails','small_thumbnails') NOT NULL DEFAULT 'large_thumbnails'";
	db()->execute($modifyThumbnail);

	$patch->finish("Updating the dev table to BotQueue production");
}
