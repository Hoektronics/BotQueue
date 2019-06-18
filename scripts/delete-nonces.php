<?php
$base_dir = dirname(__FILE__) . "/..";
$base_dir = realpath($base_dir);
require($base_dir . "/extensions/global.php");
if (defined('SENTRY_DSN')) {
    Sentry\init(['dsn' => SENTRY_DSN ]);
}
$start_time = microtime(true);

$sql = "DELETE FROM oauth_consumer_nonce WHERE `timestamp` < unix_timestamp() - 60*30";
db()->execute($sql);

//finished!!!!
echo "Cleaned old nonces in " . round((microtime(true) - $start_time), 2) . " seconds.\n";