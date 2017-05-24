<?php

include("../extensions/global.php");

if (!defined("STORAGE_PATH"))
	die("Please define the STORAGE_PATH in your config file for local uploads\n");

if (!defined("AMAZON_S3_BUCKET_NAME"))
	die("Please define the AMAZON_S3_BUCKET_NAME to pull from\n");

if (!defined("AMAZON_AWS_KEY"))
	die("Please define the AMAZON_AWS_KEY in your config\n");

if (!defined("AMAZON_AWS_SECRET"))
	die("Please define the AMAZON_AWS_SECRET in your config\n");

if (!defined("STORAGE_METHOD") || STORAGE_METHOD !== "LocalFile")
	die("Please define STORAGE_METHOD to be LocalFile\n");

$total_s3_files = db()->getValue("SELECT max(id) FROM s3_files");
print("Files to convert: " . $total_s3_files . "\n");

$rows_to_remove = array();

for ($s3_id = 1; $s3_id <= $total_s3_files; $s3_id++) {
	// Grab the s3 file
	// Verify the file exists.
	// If it exists, download it
	// If it doesn't, add it to the rows to be removed
	// Set all of the correct variables
	$s3_file = new S3File($s3_id);
	if ($s3_file->exists()) {
		$local_file = new LocalFile($s3_id);

		$temp_file = tempnam("/tmp", "BQ");

		$s3_file->download($s3_file->get('path'), $temp_file);
		$local_file->upload($temp_file, $s3_file->get('path'));
		$local_file->id = $s3_id;

		$percent_completed = sprintf("%01.2f", (($s3_id*100) / $total_s3_files));
		print(sprintf("%6s", $percent_completed) . "% => File #". $s3_id . "\n");
	} else {
		print("File #". $s3_id ." doesn't exist\n");
		$rows_to_remove[] = $s3_id;
	}
}

print(count($rows_to_remove) . " files were no longer in S3\n");