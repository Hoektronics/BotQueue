<?
include("../patches.php");

$patch = new Patch(21);

if (!$patch->exists()) {

	// Create new type column
	$sql = "ALTER TABLE oauth_token ADD COLUMN `type2` enum('request', 'verified', 'access') AFTER type";
	db()->execute($sql);

	// Kill tokens that are stuck in a bad state due to a previous issue
	$sql = "DELETE from oauth_token where type=2 AND verified=0";

	// Migrate the 3 types over
	$sql = "UPDATE oauth_token SET type2='request' WHERE type=1 AND verified=0";
	db()->execute($sql);

	$sql = "UPDATE oauth_token SET type2='verified' WHERE type=1 AND verified=1";
	db()->execute($sql);

	$sql = "UPDATE oauth_token SET type2='access' WHERE type=2 AND verified=1";
	db()->execute($sql);

	$sql = "ALTER TABLE oauth_token DROP COLUMN type";
	db()->execute($sql);
	$sql = "ALTER TABLE oauth_token DROP COLUMN verified";
	db()->execute($sql);

	$sql = "ALTER TABLE oauth_token CHANGE type2 type enum('request', 'verified', 'access')";
	db()->execute($sql);

	$patch->finish("Converting oauth token codes");
}