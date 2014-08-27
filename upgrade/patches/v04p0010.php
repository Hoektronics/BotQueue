<?
include("../../extensions/global.php");
include("../patches.php");

$patchNumber = 10;
start_patch();

if (!patch_exists($patchNumber)) {
    $statsViewSQL = "
        CREATE VIEW stats AS
        SELECT (unix_timestamp(end_date) - unix_timestamp(start_date)) AS seconds,
        bot_id, user_id, status, start_date, end_date
        FROM job_clock
        WHERE status != 'working'
        ORDER by seconds DESC
    ";
	db()->execute($statsViewSQL);

	finish_patch($patchNumber, "Adding stats view");
}
