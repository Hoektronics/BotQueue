<?
include("../patches.php");

$patch = new Patch(22);

if (!$patch->exists()) {

	$sql = "UPDATE jobs set user_sort=0 WHERE status IN (
			'complete',
			'failure',
			'canceled'
		)";
	$jobsAffected = db()->execute($sql);
	$patch->log("$jobsAffected jobs cleaned");

	$patch->finish("Cleaning up user_sort for jobs");
}