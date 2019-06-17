<?php
include("../patches.php");

$patch = new Patch(3);

if (!$patch->exists()) {
	$addCanceledSQL = "ALTER TABLE jobs
  MODIFY COLUMN status
  ENUM('available','taken','slicing','downloading','qa','complete','failure','canceled') NOT NULL DEFAULT 'available'";
	db()->execute($addCanceledSQL);

	$patch->finish("Allowing a job to be canceled");
}