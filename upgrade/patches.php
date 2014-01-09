<?

function start_patch()
{
  $createPatches = "CREATE TABLE IF NOT EXISTS `patches` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `patch_num` int(11) unsigned NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `patch_num` (`patch_num`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
  db()->execute($createPatches);
}

function finish_patch($patchNumber, $description) {
  $patch = "INSERT INTO patches
  (patch_num, description)
  VALUES(".
    db()->escape($patchNumber).",'".
    db()->escape($description)."')";
  db()->execute($patch);
  print("Patch ".$patchNumber." applied\n");
}

function patch_exists($patchNumber) {
  $patchSQL = "SELECT * from patches
  where patch_num=".db()->escape($patchNumber);
  return (db()->execute($patchSQL) > 0);
}

?>