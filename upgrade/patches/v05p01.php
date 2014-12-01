<?
include("../../extensions/global.php");
include("../patches.php");

$patchNumber = 13;
start_patch();

if (!patch_exists($patchNumber)) {

	$sql = "ALTER TABLE bots MODIFY COLUMN `error_text` text NOT NULL DEFAULT ''";

	db()->execute($sql);

	finish_patch($patchNumber, "Expanded error_text field");
}