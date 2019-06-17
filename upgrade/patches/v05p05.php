<?php
include("../patches.php");

$patch = new Patch(17);

if (!$patch->exists()) {

	$tables = array(
		'activities',
		'bots',
		'email_queue',
		'error_log',
		'job_clock',
		'jobs',
		'oauth_token',
		'queues',
		'slice_configs',
		'slice_jobs'
	);

	$sql = "ALTER TABLE slice_configs MODIFY COLUMN user_id int(11) unsigned";
	db()->execute($sql);
	$sql = "UPDATE slice_configs set user_id=NULL where user_id=0";
	db()->execute($sql);

	$sql = "ALTER TABLE bots MODIFY COLUMN user_id int(11) unsigned NOT NULL";
	db()->execute($sql);

	$sql = "ALTER TABLE email_queue MODIFY COLUMN user_id int(11) unsigned NOT NULL";
	db()->execute($sql);

	$sql = "ALTER TABLE jobs MODIFY COLUMN user_id int(11) unsigned NOT NULL";
	db()->execute($sql);

	$sql = "ALTER TABLE job_clock MODIFY COLUMN user_id int(11) unsigned NOT NULL";
	db()->execute($sql);

	$sql = "ALTER TABLE oauth_consumer MODIFY COLUMN user_id int(11) unsigned NOT NULL";
	db()->execute($sql);

	$sql = "ALTER TABLE oauth_token MODIFY COLUMN user_id int(11) unsigned";
	db()->execute($sql);
	$sql = "UPDATE oauth_token set user_id=NULL where user_id=0";
	db()->execute($sql);

	$sql = "ALTER TABLE queues MODIFY COLUMN user_id int(11) unsigned NOT NULL";
	db()->execute($sql);

	$sql = "ALTER TABLE s3_files MODIFY COLUMN user_id int(11) unsigned NOT NULL";
	db()->execute($sql);

	foreach($tables as $table) {
		$sql = "DROP INDEX user_id on $table";
		db()->execute($sql);
	}

	$tables = array_merge($tables, array(
		'comments',
		'oauth_consumer',
		's3_files',
		'tokens'
	));

	foreach($tables as $table) {
		$sql = "DELETE FROM $table WHERE user_id NOT IN (SELECT id FROM users) AND user_id IS NOT NULL";
		db()->execute($sql);
		$sql = "ALTER TABLE $table ADD FOREIGN KEY (`user_id`) REFERENCES users(`id`) ON DELETE CASCADE";
		db()->execute($sql);
	}

	$patch->finish("Added user ID constraint");
}