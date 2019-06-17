<?php
  require("../extensions/global.php");
  $start_time = microtime(true);
  
  //dump our production database
  echo "Dumping production data.\n";
  $cmd = "/usr/bin/mysqldump -u root botqueue > botqueue.sql";
  passthru($cmd);
  
  //backup our dev database
  echo "Backing up dev database.\n";
  $cmd = "/usr/bin/mysqldump -u root devqueue > devqueue.sql";
  passthru($cmd);
  
  //delete our dev db.
  db()->execute("DROP DATABASE devqueue");
  db()->execute("CREATE DATABASE devqueue");
  db()->selectDb("devqueue");
    
  //overwrite our dev database
  echo "Importing production data.\n";
  $cmd = "/usr/bin/mysql -u root devqueue < botqueue.sql";
  passthru($cmd);
  
  //clean up our login crap
  echo "Cleaning up login stuff.\n";
  db()->execute("DELETE FROM oauth_consumer_nonce");
  db()->execute("DELETE FROM tokens");
  
  //copy all our files to the new bucket.
  echo "Copying over all files.\n";
  $sql = "SELECT id FROM s3_files ORDER BY id DESC";
  $ids = db()->getArray($sql);
  foreach ($ids AS $id => $row)
  {
    $file = Storage::get($id);
    
    if ($file->exists())
    {
      echo "End of new files, done copying.\n";
      break;
    }
    
    //okay, copy it over.
	$file->copy();
    echo $id+1 . " / " . count($ids) . " / " . $file->getName() . "\n";
  }
  
  //finished!!!!
  echo "\nProduction clone complete in " . round((microtime(true) - $start_time), 2) . " seconds.\n";
?>