<?
include("../patches.php");

$patch = new Patch(12);

if (!$patch->exists()) {

	// Fix the temperature fields:
	$rs = db()->query("SELECT * from jobs");
	while ($row = $rs->fetch(PDO::FETCH_ASSOC)) {
		$job = new Job($row['id']);

		$fixed_data = fix_temp_data($job->get('temperature_data'));
		$job->set('temperature_data', $fixed_data);
		$job->save();
	}

	$rs = db()->query("SELECT * from bots");
	while ($row = $rs->fetch(PDO::FETCH_ASSOC)) {
		$bot = new Bot($row['id']);

		$fixed_data = fix_temp_data($bot->get('temperature_data'));
		$bot->set('temperature_data', $fixed_data);
		$bot->save();
	}

    $expandTemperatureData = "
		ALTER TABLE jobs
  		MODIFY COLUMN temperature_data longtext NOT NULL";
    db()->execute($expandTemperatureData);
	$expandTemperatureData = "
		ALTER TABLE bots
  		MODIFY COLUMN temperature_data longtext NOT NULL";


	$patch->finish("Expanded temperature data fields");
}

function fix_temp_data($data) {
	if(strlen($data) == 0)
		return "";

	$data .= "}";
	while(JSON::decode($data) === null) {
		// Remove last two characters
		$data = substr($data, 0, -2);

		// Add the end of the temperature data
		$data .= "}";
	}

	return $data;
}