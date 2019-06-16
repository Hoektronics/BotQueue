<?php
include("../patches.php");

$patch = new Patch(10);

if (!$patch->exists()) {
    $statsViewSQL = "
        CREATE VIEW stats AS
        SELECT (unix_timestamp(end_date) - unix_timestamp(start_date)) AS seconds,
        bot_id, user_id, status, start_date, end_date
        FROM job_clock
        WHERE status != 'working'
        ORDER by seconds DESC
    ";
	db()->execute($statsViewSQL);

	$patch->finish("Adding stats view");
}
