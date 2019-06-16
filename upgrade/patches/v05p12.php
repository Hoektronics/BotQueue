<?php
include("../patches.php");

$patch = new Patch(24);

if (!$patch->exists()) {

	$sql = "ALTER table bots ADD KEY `status` (`status`)";
	db()->execute($sql);

	$patch->finish("Adding MySQL keys");
}