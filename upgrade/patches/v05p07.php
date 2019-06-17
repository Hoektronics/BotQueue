<?php
include("../patches.php");

$patch = new Patch(19);

if (!$patch->exists()) {

	$createSql = "CREATE TABLE IF NOT EXISTS `notifications` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `timestamp` datetime NOT NULL,
			  `from_user_id` int(11) unsigned NULL,
			  `to_user_id` int(11) unsigned NULL,
			  `title` varchar(255) NOT NULL,
			  `content` text NOT NULL,
			  PRIMARY KEY (`id`),
			  FOREIGN KEY (`from_user_id`) REFERENCES users(`id`) ON DELETE CASCADE,
			  FOREIGN KEY (`to_user_id`) REFERENCES users(`id`) ON DELETE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

	db()->execute($createSql);

	$sql = "ALTER TABLE users ADD COLUMN `last_notification` int(11) NOT NULL DEFAULT 0 AFTER `registered_on`";

	db()->execute($sql);

	$content  =	"Welcome to the new notification center! I'm going to use this to let you know ";
	$content .= "about awesome new updates that are happening to BotQueue. Eventually, you will ";
	$content .= "receive updates to comments and messages from other users through this system. ";
	$content .= "I'm still working on the placement of the notification icon in the full screen ";
	$content .= "mode, because I want the icon to be to the left of the username.";
	$content .= "\n\n";
	$content .= "If you have any issues, or even suggestions, please let me know in either the ";
	$content .= "<a href=\"https://groups.google.com/forum/#!forum/botqueue\">google group</a> or at ";
	$content .= "<a href=\"https://github.com/Hoektronics/BotQueue/issues\">GitHub issues</a>.";
	$content .= "\n\n";
	$content .= "Thank you for using BotQueue!";
	$content .= "\n\n";
	$content .= " ~ Justin Nesselrotte";

	$notification = new Notification();
	$notification->set('from_user_id', null); // From the system
	$notification->set('to_user_id', null); // To everyone
	$notification->set('timestamp', date("2014-12-24 00:00:00"));
	$notification->set('title', 'New notification system');
	$notification->set('content', $content);
	$notification->save();

	$patch->finish("Added notifications table");
}