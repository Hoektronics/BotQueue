<?
  include("../../extensions/global.php");
  include("../patches.php");

  $patchNumber = 1;
  start_patch();

  // This patch is literally just the starting point for other patches
  // It's pretty much just an example

  if(!patch_exists($patchNumber)) {
    finish_patch($patchNumber, "Starting the patch system");
  }

?>
