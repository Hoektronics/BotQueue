<?
  require("../extensions/global.php");
  $start_time = microtime(true);
  
  $database = "devqueue";
  
  //dump our production database
  echo "Dumping backup data.\n";
  $cmd = "/usr/bin/mysqldump -u root {$database} > {$database}-v1.sql";
  passthru($cmd);
  
  //overwrite our dev database
  echo "Upgrading v1 to v2.\n";
  $cmd = "/usr/bin/mysql -u root {$database} < v1tov2.sql";
  passthru($cmd);
  
  //finished!!!!
  echo "\nv1 to v2 upgrade complete in " . round((microtime(true) - $start_time), 2) . " seconds.\n";
?>