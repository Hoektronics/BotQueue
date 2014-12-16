<?
include("../../extensions/global.php");
include("../patches.php");

$patchNumber = 18;
start_patch();

if (!patch_exists($patchNumber)) {

	$sql = "CREATE TABLE IF NOT EXISTS `webcam_images` (
			  `timestamp` datetime NOT NULL,
			  `image_id` bigint(11) unsigned NOT NULL,
			  `user_id` int(11) unsigned NOT NULL,
			  `bot_id` int(11) unsigned NULL,
			  `job_id` int(11) unsigned NULL,
			  PRIMARY KEY (`timestamp`, `image_id`),
			  FOREIGN KEY (`image_id`) REFERENCES s3_files(`id`) ON DELETE CASCADE,
			  FOREIGN KEY (`user_id`) REFERENCES users(`id`) ON DELETE CASCADE,
			  FOREIGN KEY (`bot_id`) REFERENCES bots(`id`) ON DELETE CASCADE,
			  FOREIGN KEY (`job_id`) REFERENCES jobs(`id`) ON DELETE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

	db()->execute($sql);

	$failCount = 0;

	$sql = "SELECT id from jobs";
	$jobsCollection = new Collection($sql);
	$jobsCollection->bindType('id', 'Job');

	$jobs = $jobsCollection->getAll();

	$total = $jobsCollection->count();
	$count = 0;
	patch_progress(0);
	foreach ($jobs as $row) {
		/** @var Job $job */
		$job = $row['Job'];
		$images_json = $job->get('webcam_images');
		if($job->isHydrated() && $images_json != "") {
			$images = json_decode($images_json, true);

			// TODO: convert this to use a sql language system with methods and not string manipulation
			$sql = "INSERT IGNORE INTO webcam_images(`timestamp`, `image_id`, `user_id`, `job_id`, `bot_id`) VALUES";
			$first = true;
			foreach ($images as $timestamp => $image_id) {
				if(!$first)
					$sql .= ",";
				$first = false;
				$sql .= "(";
				$file = Storage::get($image_id);
				if ($file->isHydrated() && $file->getUser()->isHydrated()) {
					$sql .= "'" . date("Y-m-d H:i:s", $timestamp) . "',";
					$sql .= $image_id . ",";
					$sql .= $job->getUser()->id . ",";
					$sql .= $job->id . ",";
					$bot = $job->getBot();
					if ($bot->isHydrated()) {
						$sql .= $bot->id;
					} else {
						$sql .= "NULL";
					}
					$sql .= ")";
				} else {
					$failCount++;
				}
			}
			db()->execute($sql);
			$count++;
			patch_progress((int)(($count*100)/$total));
		}
	}

	if($failCount > 0) {
		patch_log($failCount . " images no longer exist in the database");
	}

	finish_patch($patchNumber, "Added webcam images table");
}