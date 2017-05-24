<?
include("../patches.php");

$patch = new Patch(9);

if (!$patch->exists()) {
	$addDroppedSQL = "ALTER TABLE job_clock
  		MODIFY COLUMN status
  		ENUM('idle','slicing','working','waiting','error','maintenance','offline', 'dropped')";
	db()->execute($addDroppedSQL);

	$patch->finish("Adding dropped to the job_clock");
}
