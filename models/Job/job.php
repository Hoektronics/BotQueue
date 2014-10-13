<?php

/*
	This file is part of BotQueue.

	BotQueue is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	BotQueue is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with BotQueue.  If not, see <http://www.gnu.org/licenses/>.
  */

class Job extends Model
{
	public function __construct($id = null)
	{
		parent::__construct($id, "jobs");
	}

	public function getName()
	{
		return basename($this->get('name'));
	}

	public function setName($name)
	{
		$this->set('name', $name);
	}

	public function setStatus($status)
	{
		$this->set('status', $status);
	}

	public function getUser()
	{
		return new User($this->get('user_id'));
	}

	public function getUrl()
	{
		return "/job:" . $this->id;
	}

	public function getStatusHTML()
	{
		return "<span class=\"label " . JobStatus::getStatusHTMLClass($this->get('status')) . "\">" . $this->get('status') . "</span>";
	}

	public function getWebcamImage()
	{
		return Storage::get($this->get('webcam_image_id'));
	}

	public function getSourceFile()
	{
		return Storage::get($this->get('source_file_id'));
	}

	public function getSliceJob()
	{
		return new SliceJob($this->get('slice_job_id'));
	}

	public function getFile()
	{
		return Storage::get($this->get('file_id'));
	}

	public function getQueue()
	{
		return new Queue($this->get('queue_id'));
	}

	public function getBot()
	{
		return new Bot($this->get('bot_id'));
	}

	public function getComments()
	{
		return Comment::byContentAndType($this->id, 'job');
	}

	public function getLatestTimeLog()
	{
		$sql = "SELECT id FROM job_clock WHERE job_id = ? AND status = 'working' ORDER BY id DESC";
		$id = db()->getValue($sql, array($this->id));

		return new JobClockEntry($id);
	}

	public function getAPIData()
	{
		$d = array();
		$d['id'] = $this->id;
		$d['bot_id'] = $this->get('bot_id');
		$d['name'] = $this->getName();
		$d['queue'] = $this->get('queue_id');
		$d['source_file'] = $this->getSourceFile()->getAPIData();
		$d['file'] = $this->getFile()->getAPIData();
		$d['slicejob'] = $this->getSliceJob()->getAPIData();
		$d['status'] = $this->get('status');
		$d['created_time'] = $this->get('created_time');
		$d['taken_time'] = $this->get('taken_time');
		$d['downloaded_time'] = $this->get('downloaded_time');
		$d['finished_time'] = $this->get('finished_time');
		$d['verified_time'] = $this->get('verified_time');
		$d['progress'] = $this->get('progress');

		$webcam = $this->getWebcamImage();
		if ($webcam->isHydrated())
			$d['webcam'] = $webcam->getAPIData();

		return $d;
	}

	public function isMine()
	{
		if ($this->get('user_id') == User::$me->id)
			return true;
		return false;
	}

	public function canView()
	{
		return $this->isMine();
	}

	public function canEdit()
	{
		return $this->canView();
	}

	public function canComment()
	{
		return $this->canView();
	}

	public function cancelJob()
	{
		$bot = $this->getBot();
		if ($bot->isHydrated()) {
			$bot->reset();
		}

		$this->setStatus('canceled');
		$this->set('bot_id', 0);
		$this->set('finished_time', date("Y-m-d H:i:s"));
		$this->save();
	}

	public function pushToTop()
	{
		//Prevent there from being a user_sort value of 0 for now
		//todo Make this process better
		$sql = "UPDATE jobs SET user_sort=user_sort+1 WHERE user_id = ?";
		db()->execute($sql, array($this->get('user_id')));

		// Find the minimum value and get the slot before it
		$sql = "SELECT min(user_sort)-1 FROM jobs WHERE user_id = ?";
		$min = (int)db()->getValue($sql, array($this->get('user_id')));

		$this->set('user_sort', $min);
		$this->save();
	}

	public function getElapsedTime()
	{
		if ($this->get('status') == 'available') {
			$start = strtotime($this->get('created_time'));
			$end = time();
		} elseif ($this->get('status') == 'taken' || $this->get('status') == 'downloading' || $this->get('status') == 'slicing') {
			$start = strtotime($this->get('taken_time'));
			$end = time();
		} elseif ($this->get('status') == 'canceled') {
			$start = 0;
			$end = $start;
		} else {
			$start = strtotime($this->get('taken_time'));
			$end = strtotime($this->get('finished_time'));
		}

		return $end - $start;
	}

	public function getElapsedText()
	{
		return Utility::getElapsedShort($this->getElapsedTime());
	}

	public function getEstimatedTime()
	{
		//okay, now estimate it for us.
		$elapsed = $this->getElapsedTime();
		if ($this->get('progress') > 0) {
			$total = (100 / $this->get('progress')) * $elapsed;
			return $total - $elapsed;
		}

		return 0;
	}

	public function getEstimatedText()
	{
		return Utility::getElapsedShort($this->getEstimatedTime());
	}

	public function logError($error)
	{
		$log = new ErrorLog();
		$log->set('user_id', User::$me->id);
		$log->set('job_id', $this->id);
		$log->set('queue_id', $this->get('queue_id'));
		$log->set('bot_id', $this->get('bot_id'));
		$log->set('reason', $error);
		$log->set('error_date', date("Y-m-d H:i:s"));
		$log->save();

		return $log;
	}

	public function getErrorLog()
	{
		$sql = "SELECT id
				FROM error_log
				WHERE job_id = ?
				ORDER BY error_date DESC";

		$logs = new Collection($sql, array($this->id));
		$logs->bindType('id', 'ErrorLog');

		return $logs;
	}

	public static function getJobsRequiringAction()
	{
		$sql = "SELECT id, queue_id, bot_id
				FROM jobs
				WHERE status = 'qa'
				ORDER BY finished_time ASC";

		$jobs = new Collection($sql);
		$jobs->bindType('id', 'Job');
		$jobs->bindType('queue_id', 'Queue');
		$jobs->bindType('bot_id', 'Bot');

		return $jobs;
	}

	public function delete()
	{
		// Clean up our bot just in case we try to delete the job while it's active
		// It shouldn't be possible, but I'm not so sure yet
		$bot = $this->getBot();
		if ($bot->isHydrated()) {
			$bot->set('job_id', 0);
			$bot->setStatus(BotState::Idle);
			$bot->save();
		}

		$sql = "DELETE FROM error_log WHERE job_id = ?";
		db()->execute($sql, array($this->id));

		$sql = "DELETE FROM slice_jobs WHERE job_id = ?";
		db()->execute($sql, array($this->id));

		parent::delete();
	}

	public function reset()
	{
		if ($this->getSliceJob()->isHydrated()) {
			$this->getSliceJob()->delete();
			$this->set('slice_job_id', 0);
			$this->set('file_id', 0);
		}

		//clear out our data for the next bot.
		$this->setStatus(JobState::Available);
		$this->set('bot_id', 0);
		$this->set('taken_time', 0);
		$this->set('downloaded_time', 0);
		$this->set('finished_time', 0);
		$this->set('verified_time', 0);
		$this->set('progress', 0);
		$this->set('temperature_data', '');
		$this->save();
	}

	/**
	 * @param $queue_id int
	 * @param $file StorageInterface
	 * @return Job
	 */
	public static function addFileToQueue($queue_id, $file)
	{
		$sort = db()->getValue("SELECT max(id)+1 FROM jobs");

		// Special case for first sort value
		if ($sort == "") {
			$sort = 1;
		}

		$job = new Job();
		$job->set('user_id', User::$me->id);
		$job->set('queue_id', $queue_id);
		$job->set('source_file_id', $file->id);
		if ($file->isGCode())
			$job->set('file_id', $file->id);
		$job->setName($file->get('path'));
		$job->setStatus('available');
		$job->set('created_time', date("Y-m-d H:i:s"));
		$job->set('user_sort', $sort);
		$job->save();

		return $job;
	}

	public function complete()
	{
		$this->setStatus('qa');
		$this->set('progress', 100);
		$this->set('finished_time', date('Y-m-d H:i:s'));
		$this->save();

		$log = $this->getLatestTimeLog();
		if ($log->isHydrated()) {
			$log->set('end_date', date("Y-m-d H:i:s"));
			$log->setStatus('complete');
			$log->save();
		}
	}

}