<?php
$base_dir = dirname(__FILE__) . "/..";
$base_dir = realpath($base_dir);
require($base_dir . "/extensions/global.php");

class Patch
{
	private $patchNumber;
	private $startTime;
	private $endTime;

	public function __construct($patchNumber)
	{
		$this->patchNumber = $patchNumber;
		$this->startTime = microtime(true);
	}

	public function finish($description) {
		$patch = "INSERT INTO patches (patch_num, description) VALUES(?,?)";
		db()->execute($patch, array($this->patchNumber, $description));
		$this->endTime = microtime(true);
		$totalTime = round($this->endTime - $this->startTime, 3);

		print "Patch $this->patchNumber applied in $totalTime s: $description\n";
	}

	function exists()
	{
		$patchSQL = "SELECT * FROM patches WHERE patch_num >= ?";
		return (db()->execute($patchSQL, array($this->patchNumber)) > 0);
	}

	function log($message)
	{
		print(" > $message\n");
	}

	function progress($progress)
	{
		$currentTime = round(microtime(true) - $this->startTime, 3);
		print " > " . number_format($progress, 2) . "% $currentTime seconds elapsed      \r";
	}
}