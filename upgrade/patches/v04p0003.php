<?
include("../../extensions/global.php");
include("../patches.php");

$patchNumber = 3;
start_patch();

if(!patch_exists($patchNumber)) {
  $addCanceledSQL = "alter table jobs
  modify column status
  enum('available','taken','slicing','downloading','qa','complete','failure','canceled') NOT NULL DEFAULT 'available'";
  db()->execute($addCanceledSQL
  );

  finish_patch($patchNumber, "Allowing a job to be canceled");
}

?>
