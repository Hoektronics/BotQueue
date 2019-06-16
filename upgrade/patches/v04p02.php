<?php
include("../patches.php");

$patch = new Patch(2);

if (!$patch->exists()) {
	$addRetirementSQL = "ALTER TABLE bots
  MODIFY COLUMN status
  ENUM('idle','slicing','working','waiting','error','maintenance','offline','retired')
  DEFAULT 'idle'";
	db()->execute($addRetirementSQL);

	$patch->finish("Allowing a bot to be retired");
}
