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

	foreach ($jobs as $row) {
		/** @var Job $job */
		$job = $row['Job'];
		$images_json = $job->get('webcam_images');
		if($job->isHydrated() && $images_json != "") {
			$images = json_decode($images_json, true);
			foreach ($images as $timestamp => $image_id) {
				$file = Storage::get($image_id);
				if ($file->isHydrated() && $file->getUser()->isHydrated()) {
					$image = new WebcamImage();
					$image->set('timestamp', date("Y-m-d H:i:s", $timestamp));
					$image->set('image_id', $image_id);
					$image->set('user_id', $job->getUser()->id);
					$image->set('job_id', $job->id);
					$bot = $job->getBot();
					if ($bot->isHydrated()) {
						$image->set('bot_id', $bot->id);
					}
					$image->save();
				} else {
					$failCount++;
				}
			}
		}
	}

	if($failCount > 0) {
		patch_log($failCount . " images no longer exist in the database");
	}

	finish_patch($patchNumber, "Added webcam images table");
}