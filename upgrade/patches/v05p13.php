<?php
include("../patches.php");

$patch = new Patch(25);

if (!$patch->exists()) {

	$sql = "ALTER table bots ADD KEY `name` (`name`)";
	db()->execute($sql);

	$patch->finish("Adding MySQL keys");
}