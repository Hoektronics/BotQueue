<?
include("../../extensions/global.php");
include("../patches.php");

$patchNumber = 2;
start_patch();

if(!patch_exists($patchNumber)) {
  $addRetirementSQL = "alter table bots
  modify column status
  enum('idle','slicing','working','waiting','error','maintenance','offline','retired')
  default 'idle'";
  db()->execute($addRetirementSQL);

  finish_patch($patchNumber, "Allowing a bot to be retired");
}

?>
