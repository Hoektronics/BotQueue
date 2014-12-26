<?
include("../patches.php");

$patch = new Patch(20);

if (!$patch->exists()) {
	$emptySql = "SELECT count(id) FROM s3_files WHERE add_date='0000-00-00 00:00:00'";

	$originalCount = db()->getValue($emptySql);

	$patch->log("Starting with $originalCount files with invalid dates.");

	// Fix known images
	$sql = "UPDATE s3_files, webcam_images
			SET s3_files.add_date=webcam_images.timestamp
			WHERE s3_files.id = webcam_images.image_id
			AND s3_files.add_date='0000-00-00 00:00:00'";
	$currentCount = fix_timestamp($sql, "Known image timestamps", $originalCount);

	// Fix gcode files uploaded directly
	$sql = "UPDATE s3_files, jobs
			SET s3_files.add_date=jobs.created_time
			WHERE jobs.source_file_id=s3_files.id
			AND s3_files.add_date='0000-00-00 00:00:00'";
	$currentCount = fix_timestamp($sql, "Known gcode files", $currentCount);

	// Fix gcode files made by a slicer
	$sql = "UPDATE s3_files, jobs, slice_jobs
			SET s3_files.add_date=slice_jobs.finish_date
			WHERE jobs.id=slice_jobs.job_id
			AND slice_jobs.input_id=jobs.source_file_id
			AND slice_jobs.output_id=jobs.file_id
            AND s3_files.id=jobs.file_id
			AND slice_jobs.finish_date!='0000-00-00 00:00:00'
			AND s3_files.add_date='0000-00-00 00:00:00'";
	$currentCount = fix_timestamp($sql, "Sliced gcode files", $currentCount);

	// Fix files if a child has a timestamp, but the parent doesn't.
	$sql = "UPDATE s3_files a, s3_files b
			SET a.add_date=b.add_date
			WHERE a.id=b.parent_id
			AND b.parent_id!=0
			AND b.add_date!='0000-00-00 00:00:00'
			AND a.add_date='0000-00-00 00:00:00'";
	$currentCount = fix_timestamp($sql, "Fixing parent files", $currentCount);

	// Fix files if a parent has a timestamp, but the child doesn't.
	$sql = "UPDATE s3_files a, s3_files b
			SET a.add_date=b.add_date
			WHERE a.parent_id=b.id
			AND a.parent_id!=0
			AND b.add_date!='0000-00-00 00:00:00'
			AND a.add_date='0000-00-00 00:00:00'";
	$currentCount = fix_timestamp($sql, "Fixing child files", $currentCount);

	$patch->log("Total: ".($originalCount-$currentCount)." fixed, $currentCount remaining");

	$patch->finish("Fixing bad timestamps for s3_files");
}

function fix_timestamp($sql, $method, $lastCount) {
	global $patch, $emptySql;

	db()->execute($sql);
	$currentCount = db()->getValue($emptySql);
	$patch->log("$method: ".($lastCount - $currentCount)." fixed");
	return $currentCount;
}