<?php
include("../patches.php");

$patch = new Patch(1);

// Special case, we create it if it doesn't exist, so no error occurs.
$createPatches = "CREATE TABLE IF NOT EXISTS `patches` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `patch_num` INT(11) UNSIGNED NOT NULL,
    `description` TEXT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `patch_num` (`patch_num`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
db()->execute($createPatches);

if (!$patch->exists()) {
	$patch->finish("Starting the patch system");
}