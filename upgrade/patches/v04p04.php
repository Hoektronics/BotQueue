<?
include("../patches.php");

$patch = new Patch(4);

if (!$patch->exists()) {
	$addPausedSQL = "ALTER TABLE bots
  MODIFY COLUMN status
  ENUM('idle','slicing','working','paused','waiting','error','maintenance','offline','retired') DEFAULT 'idle'";
	db()->execute($addPausedSQL);

	$patch->finish("Allowing a bot to be paused");
}