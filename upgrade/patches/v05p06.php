<?
include("../patches.php");

$patch = new Patch(18);

if (!$patch->exists()) {

	$rowSql = "CREATE TABLE IF NOT EXISTS `webcam_images` (
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

	db()->execute($rowSql);

	$failCount = 0;

	$rowSql = "SELECT id from jobs where webcam_images!=''";
	$jobsCollection = new Collection($rowSql);
	$jobsCollection->bindType('id', 'Job');

	$jobs = $jobsCollection->getAll();

	$total = $jobsCollection->count();
	$count = 0;
	$patch->progress(0);

	// Get the current webcam images in an array, so we can quickly skip those.
	$pdoStatement = db()->query("SELECT image_id from webcam_images");
	$existingImages = array();
	foreach ($pdoStatement->fetchAll(PDO::FETCH_ASSOC) as $row) {
		$existingImages[$row['image_id']] = true;
	}

	foreach ($jobs as $row) {
		/** @var Job $job */
		$job = $row['Job'];
		$images_json = $job->get('webcam_images');
		if($job->isHydrated() && $images_json != "") {
			$images = json_decode($images_json, true);

			$rowData = array();
			foreach ($images as $timestamp => $image_id) {
				if(!array_key_exists($image_id, $existingImages)) {
					$file = Storage::get($image_id);
					if ($file->isHydrated() && $file->getUser()->isHydrated()) {
						$user_id = $job->getUser()->id;
						$rowSql = "('" . date("Y-m-d H:i:s", $timestamp) . "', ";
						$rowSql .= "$image_id, $user_id, $job->id, ";
						$bot = $job->getBot();
						if ($bot->isHydrated()) {
							$rowSql .= "$bot->id";
						} else {
							$rowSql .= "NULL";
						}
						$rowSql .= ")";
						$rowData[] = $rowSql;
					} else {
						$failCount++;
					}
				} else {
					// Remove it from the array to save memory
					unset($existingImages[$image_id]);
				}
			}
			if(count($rowData) > 0)
				db()->execute("INSERT IGNORE INTO webcam_images(`timestamp`, `image_id`, `user_id`, `job_id`, `bot_id`) VALUES " . implode(",", $rowData));
			$count++;
			$patch->progress(($count*100)/$total);
		}
	}

	if($failCount > 0) {
		$patch->log($failCount . " images no longer exist in the database");
	}

	$patch->finish("Added webcam images table");
}