<?
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
    $s3 = new S3File($row['id']);
    
    if ($s3->exists(AMAZON_S3_BUCKET_NAME))
    {
      echo "End of new files, done copying.\n";
      break;
    }
    
    //okay, copy it over.
    $s3->copyToBucket(AMAZON_S3_BUCKET_NAME);
    echo $id+1 . " / " . count($ids) . " / " . $s3->getName() . "\n";
  }
  
  //overwrite our dev database
  echo "Upgrading v1 to v2.\n";
  $cmd = "/usr/bin/mysql -u root devqueue < v1tov2.sql";
  passthru($cmd);
  
  //temporary hack to make debug easier
  db()->execute("UPDATE bots SET slice_config_id = 1, slice_engine_id = 1");
  
  //finished!!!!
  echo "\nProduction clone complete in " . round((microtime(true) - $start_time), 2) . " seconds.\n";
?>