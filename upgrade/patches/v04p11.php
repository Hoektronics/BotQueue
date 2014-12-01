<?
include("../../extensions/global.php");
include("../patches.php");

$patchNumber = 11;
start_patch();

if (!patch_exists($patchNumber)) {
    $addDroppedSQL = "ALTER TABLE job_clock
  		MODIFY COLUMN status
  		enum('working','waiting', 'complete', 'dropped')";
    db()->execute($addDroppedSQL);

	finish_patch($patchNumber, "Changing job_clock enum");
}
