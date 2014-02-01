<?
include("../../extensions/global.php");
include("../patches.php");

$patchNumber = 4;
start_patch();

if(!patch_exists($patchNumber)) {
  $addPausedSQL = "alter table bots
  modify column status
  enum('idle','slicing','working','paused','waiting','error','maintenance','offline','retired') DEFAULT 'idle'";
  db()->execute($addPausedSQL);

  finish_patch($patchNumber, "Allowing a bot to be paused");
}

?>
