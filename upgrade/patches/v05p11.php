<?php
include("../patches.php");

$patch = new Patch(23);

if (!$patch->exists()) {

	$sql = "ALTER table oauth_token ADD KEY `token` (`token`)";
	db()->execute($sql);

	$patch->finish("Adding MySQL keys");
}