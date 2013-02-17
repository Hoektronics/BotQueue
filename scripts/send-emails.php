<?
  require("../extensions/global.php");
  $start_time = microtime(true);
  
  //get and send our emails.
  $emails = 0;
  $to_send = Email::getQueuedEmails()->getAll();
  foreach ($to_send AS $row)
  {
    $email = $row['Email'];
    
    if ($email->send())
    {
      $emails++;
      echo "Email #{$email->id} sent to " . $email->get('to_address') . "\n";
    }
    else
    {
      echo "Error sending email #{$email->id} to " . $email->get('to_address') . "\n";
    }
  }
  
  //finished!!!!
  echo "\nSent $emails emails in " . round((microtime(true) - $start_time), 2) . " seconds.\n";
?>