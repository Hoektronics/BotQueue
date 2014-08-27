<?
require("../extensions/global.php");
$start_time = microtime(true);

//delete our dev db.
db()->execute("TRUNCATE TABLE job_clock");

$rs = db()->query("SELECT * FROM jobs");
while ($row = $rs->fetch(PDO::FETCH_ASSOC)) {
	$log = new JobClockEntry();
	$log->set('job_id', $row['id']);
	$log->set('bot_id', $row['bot_id']);
	$log->set('user_id', $row['user_id']);
	$log->set('queue_id', $row['queue_id']);

	if ($row['status'] == 'complete' || $row['status'] == 'failure' || $row['status'] == 'qa') {
		$log->set('start_date', $row['taken_time']);
		$log->set('end_date', $row['finished_time']);
		$log->setStatus('complete');
		$log->save();
	} else if ($row['status'] == 'taken' || $row['status'] == 'slicing') {
		$log->set('start_date', $row['taken_time']);
		$log->setStatus('working');
		$log->save();
	} else if ($row['status'] == 'cancelled') {
        $log->set('start_date', $row['taken_time']);
        $log->set('end_date', $row['finished_time']);
        $log->setStatus('dropped');
        $log->save();
    }
}

//finished!!!!
echo "\nPopulated job clock log in " . round((microtime(true) - $start_time), 2) . " seconds.\n";