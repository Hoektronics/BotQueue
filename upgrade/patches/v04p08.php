<?php
include("../patches.php");

$patch = new Patch(8);

if (!$patch->exists()) {
    $removeSlicerDescription = "alter table slice_engines drop column engine_description";
	db()->execute($removeSlicerDescription);

	$patch->finish("Removing the engine_description");
}
