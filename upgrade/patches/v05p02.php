<?php
include("../patches.php");

$patch = new Patch(14);

if (!$patch->exists()) {

	$sql = "CREATE TABLE IF NOT EXISTS `bot_queues` (
		        `queue_id` INT(11) UNSIGNED NOT NULL,
		        `bot_id` INT(11) UNSIGNED NOT NULL,
		        `priority` INT(11) UNSIGNED NOT NULL,
		        PRIMARY KEY (`queue_id`, `bot_id`, `priority`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8";

	db()->execute($sql);

	$sql = "SELECT id, queue_id from bots";

	$bots = new Collection($sql);
	$bots->bindType("id", "Bot");
	$bots->bindType("queue_id", "Queue");
	foreach($bots->getAll() as $row) {
		$bot = $row['Bot'];
		$queue = $row['Queue'];
		$sql = "INSERT INTO bot_queues VALUES(?, ?, 1)";
		$data = array($queue->id, $bot->id);
		db()->execute($sql, $data);
	}

	$sql = "DROP INDEX queue_id ON bots";
	db()->execute($sql);
	$sql = "ALTER TABLE bots DROP COLUMN queue_id";
	db()->execute($sql);

	$patch->finish("Added bots to queues");
}