<?php
include("../patches.php");

$patch = new Patch(16);

if (!$patch->exists()) {

	$tables = array(
		'activities',
		'bots',
		'comments',
		'email_queue',
		'error_log',
		'job_clock',
		'jobs',
		'oauth_consumer',
		'oauth_consumer_nonce',
		'oauth_token',
		'queues',
		's3_files',
		'shortcodes',
		'slice_configs',
		'slice_engines',
		'engine_os',
		'slice_jobs',
		'tokens',
		'users',
		'patches',
		'bot_queues'
	);

	foreach($tables as $table) {
		$sql = "ALTER TABLE $table ENGINE=InnoDB";
		db()->execute($sql);
	}

	$patch->finish("Converted tables to InnoDB");
}