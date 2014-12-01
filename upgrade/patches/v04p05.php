<?
include("../../extensions/global.php");
include("../patches.php");

$patchNumber = 5;
start_patch();

if (!patch_exists($patchNumber)) {
	$addContentIDKey = "ALTER TABLE comments ADD KEY `content_id` (`content_id`)";
	db()->execute($addContentIDKey);

	$addContentTypeKey = "ALTER TABLE comments ADD KEY `content_type` (`content_type`)";
	db()->execute($addContentTypeKey);

	$addWebcamID = "alter table jobs add column `webcam_image_id` int(11) unsigned NOT NULL DEFAULT '0' after verified_time;";
	db()->execute($addWebcamID);

	$addWebcamImages = "alter table jobs add column `webcam_images` text NOT NULL after webcam_image_id";
	db()->execute($addWebcamImages);

	$dropTimestamp = "alter table oauth_consumer_nonce drop index timestamp";
	db()->execute($dropTimestamp);

	$dropNonce = "alter table oauth_consumer_nonce drop index nonce";
	db()->execute($dropNonce);

	$addIPAddressKey = "alter table oauth_token add KEY `ip_address` (`ip_address`)";
	db()->execute($addIPAddressKey);

	$addParentIDKey = "alter table s3_files add KEY `parent_id` (`parent_id`)";
	db()->execute($addParentIDKey);

	$modifyThingiverseToken = "alter table users modify `thingiverse_token` varchar(40) NOT NULL DEFAULT ''";
	db()->execute($modifyThingiverseToken);

	$modifyThumbnail = "alter table users modify `dashboard_style` enum('list','large_thumbnails','medium_thumbnails','small_thumbnails') NOT NULL DEFAULT 'large_thumbnails'";
	db()->execute($modifyThumbnail);

	finish_patch($patchNumber, "Updating the dev table to BotQueue production");
}

?>
