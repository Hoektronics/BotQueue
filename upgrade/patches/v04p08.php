<?
include("../../extensions/global.php");
include("../patches.php");

$patchNumber = 8;
start_patch();

if (!patch_exists($patchNumber)) {
    $removeSlicerDescription = "alter table slice_engines drop column engine_description";
	db()->execute($removeSlicerDescription);

	finish_patch($patchNumber, "Removing the engine_description");
}

?>
