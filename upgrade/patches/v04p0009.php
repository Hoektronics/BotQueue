<?
include("../../extensions/global.php");
include("../patches.php");

$patchNumber = 9;
start_patch();

if (!patch_exists($patchNumber)) {
	$addDroppedSQL = "ALTER TABLE job_clock
  		MODIFY COLUMN status
  		ENUM('idle','slicing','working','waiting','error','maintenance','offline', 'dropped')";
	db()->execute($addDroppedSQL);

	finish_patch($patchNumber, "Adding dropped to the job_clock");
}
