<?
include("../patches.php");

$patch = new Patch(13);

if (!$patch->exists()) {

	$sql = "ALTER TABLE bots MODIFY COLUMN `error_text` text NOT NULL DEFAULT ''";

	db()->execute($sql);

	$patch->finish("Expanded error_text field");
}