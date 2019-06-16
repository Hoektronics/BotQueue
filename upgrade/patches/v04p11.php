<?php
include("../patches.php");

$patch = new Patch(11);

if (!$patch->exists()) {
    $addDroppedSQL = "ALTER TABLE job_clock
  		MODIFY COLUMN status
  		enum('working','waiting', 'complete', 'dropped')";
    db()->execute($addDroppedSQL);

	$patch->finish("Changing job_clock enum");
}
