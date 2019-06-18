<?php

$base_dir = dirname(__FILE__) . "/..";
$base_dir = realpath($base_dir);
require($base_dir . "/extensions/global.php");
if (defined('SENTRY_DSN')) {
    Sentry\init(['dsn' => SENTRY_DSN ]);
}

$start_time = microtime(true);

//get and send our emails.
$emails = 0;
$to_send = Email::getQueuedEmails()->getAll();
foreach ($to_send AS $row) {
	/** @var Email $email */
	$email = $row['Email'];

	if ($email->send()) {
		$emails++;
		echo "Email #{$email->id} sent to " . $email->get('to_email') . "\n";
	} else {
		echo "Error sending email #{$email->id} to " . $email->get('to_email') . "\n";
	}
}

//finished!!!!
echo "\nSent $emails emails in " . round((microtime(true) - $start_time), 2) . " seconds.\n";