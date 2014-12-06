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

class Bot extends Model
{
	public function __construct($id = null)
	{
		parent::__construct($id, "bots");
	}

	public function getName()
	{
		return $this->get('name');
	}

	public function getStatus()
	{
		return $this->get('status');
	}

	/**
	 * @param $status string
	 * @throws InvalidStateChange
	 */
	public function setStatus($status)
	{
		$invalidStateChange = false;

		if ($status == $this->getStatus())
			return;

		if ($this->getStatus() == "") {
			switch ($status) {
				case BotState::Offline:
					break;
				default:
					$invalidStateChange = true;
			}
		} else if ($this->getStatus() == BotState::Idle) {
			switch ($status) {
				case BotState::Offline:
				case BotState::Error:
				case BotState::Maintenance:
				case BotState::Working:
				case BotState::Slicing:
					break;
				default:
					$invalidStateChange = true;
			}
		} else if ($this->getStatus() == BotState::Slicing) {
			switch ($status) {
				case BotState::Idle:
				case BotState::Waiting:
				case BotState::Working:
				case BotState::Paused:
				case BotState::Error:
				case BotState::Offline:
					break;
				default:
					$invalidStateChange = true;
			}
		} else if ($this->getStatus() == BotState::Working) {
			switch ($status) {
				case BotState::Slicing:
				case BotState::Error:
				case BotState::Idle:
				case BotState::Paused:
				case BotState::Waiting:
				case BotState::Maintenance:
				case BotState::Offline:
					break;
				default:
					$invalidStateChange = true;
			}
		} else if ($this->getStatus() == BotState::Paused) {
			switch ($status) {
				case BotState::Idle:
				case BotState::Working:
				case BotState::Slicing:
				case BotState::Maintenance:
				case BotState::Offline:
					break;
				default:
					$invalidStateChange = true;
			}
		} else if ($this->getStatus() == BotState::Waiting) {
			switch ($status) {
				case BotState::Idle:
				case BotState::Working:
				case BotState::Error:
					break;
				default:
					$invalidStateChange = true;
			}
		} else if ($this->getStatus() == BotState::Error) {
			switch ($status) {
				case BotState::Idle:
				case BotState::Offline:
				case BotState::Maintenance:
					break;
				default:
					$invalidStateChange = true;
			}
		} else if ($this->getStatus() == BotState::Maintenance) {
			switch ($status) {
				case BotState::Idle:
				case BotState::Offline:
					break;
				default:
					$invalidStateChange = true;
			}
		} else if ($this->getStatus() == BotState::Offline) {
			switch ($status) {
				case BotState::Idle:
				case BotState::Error:
				case BotState::Retired:
				case BotState::Maintenance:
					break;
				default:
					$invalidStateChange = true;
			}
		} else if ($this->getStatus() == BotState::Retired) {
			$invalidStateChange = true;
		}

		if ($invalidStateChange) {
			throw new InvalidStateChange("Cannot change Bot #" . $this->id . "'s status from {$this->getStatus()} to {$status}");
		} else {
			$this->set('status', $status);
		}
	}

	public function getUser()
	{
		return new User($this->get('user_id'));
	}

	public function getAPIData()
	{
		$r = array();
		$r['id'] = $this->id;
		$r['identifier'] = $this->get('identifier');
		$r['name'] = $this->getName();
		$r['manufacturer'] = $this->get('manufacturer');
		$r['model'] = $this->get('model');
		$r['status'] = $this->getStatus();
		$r['last_seen'] = $this->get('last_seen');
		$r['error_text'] = $this->get('error_text');

		$queues = $this->getQueues()->getAll();
		$data = array();
		if(!empty($queues)) {
			foreach($queues AS $row) {
				/** @var Queue $queue */
				$queue = $row['Queue'];
				$data[] = $queue->getAPIData();
			}
			$r['queues'] = $data;
		}

		$webcam = $this->getWebcamImage();
		if ($webcam->isHydrated())
			$r['webcam'] = $webcam->getAPIData();

		$job = $this->getCurrentJob();
		if ($job->isHydrated())
			$r['job'] = $job->getAPIData();
		else
			$r['job'] = array();

		//pull in and harmonize our config.
		$r['driver_config'] = $this->getDriverConfig();

		return $r;
	}

	public function getDriverConfig()
	{
		//load up our config
		$config = json::decode($this->get('driver_config'));
		if (!is_object($config))
			$config = new stdClass;
		$config->name = $this->getName();

		//default our slicing value
		if (!isset($config->can_slice))
			$config->can_slice = True;

		return $config;
	}

	public function getUrl()
	{
		return "/bot:" . $this->id;
	}

	public function getApp()
	{
		return new OAuthToken($this->get('oauth_token_id'));
	}

	public function getCurrentJob()
	{
		return new Job($this->get('job_id'));
	}

	public function getWebCamImage()
	{
		return Storage::get($this->get('webcam_image_id'));
	}

	public function getJobs($status = null, $sortField = 'user_sort', $sortOrder = 'ASC')
	{
		$sql = "SELECT id FROM jobs WHERE bot_id = ? ";

		$data = array($this->id);

		if ($status !== null) {
			$sql .= "AND status = ? ";
			$data[] = $status;
		}

		$sql .= "ORDER BY {$sortField} " . $sortOrder;

		$jobs = new Collection($sql, $data);
		$jobs->bindType('id', 'Job');

		return $jobs;
	}

	public function getJobClocks($status = null, $sortField = 'id', $sortOrder = 'ASC')
	{
		$sql = "SELECT id FROM job_clock WHERE bot_id = ? ";

		$data = array($this->id);

		if ($status !== null) {
			$sql .= "AND status = ? ";
			$data[] = $status;
		}

		$sql .= "ORDER BY {$sortField} " . $sortOrder;

		$jobClocks = new Collection($sql, $data);
		$jobClocks->bindType('id', 'JobClockEntry');

		return $jobClocks;
	}


	/*
	 * @return Collection
	 */
	public function getErrorLog()
	{
		$sql = "SELECT id
		    	FROM error_log
		    	WHERE bot_id = ?
		    	ORDER BY error_date DESC";

		$logs = new Collection($sql, array($this->id));
		$logs->bindType('id', 'ErrorLog');

		return $logs;
	}

	public function isMine()
	{
		return (User::$me->id == $this->get('user_id'));
	}

	/**
	 * @param $job Job
	 * @return bool
	 */
	public function canGrab($job)
	{
		//if we're already the owner, we can grab the job.
		//this is because sometimes the grab request times out and the bot doesn't know it hasn't grabbed the job.
		if ($job->get('status') == 'taken' && $job->get('bot_id') == $this->id)
			return true;

		if ($this->get('user_id') != $job->getQueue()->get('user_id'))
			return false;

		if ($job->get('status') != 'available')
			return false;

		if ($this->getStatus() != 'idle')
			return false;

		if ($this->get('job_id'))
			return false;

		if (!$this->getDriverConfig()->can_slice &&
			!$job->getFile()->isHydrated()
		)
			return false;

		$sql = "select DATE_SUB(NOW(), INTERVAL queues.delay SECOND) > MAX(jobs.taken_time)
			FROM queues, jobs
			WHERE queues.id = jobs.queue_id";
		if(db()->getValue($sql) == 0)
			return false;

		return true;
	}

	/**
	 * @param $job Job
	 * @param $can_slice bool
	 * @return Job
	 * @throws Exception
	 */
	public function grabJob($job, $can_slice = true)
	{
		$grabAttemptSQL = "
            UPDATE jobs
            SET bot_id =
              CASE
                WHEN bot_id=0
                THEN
                  ?
                ELSE
                  bot_id
              END
            WHERE id = ?
        ";

		// Attempt to grab the job unless another bot already has
		db()->execute($grabAttemptSQL, array($this->id, $job->id));

		$job = new Job($job->id); // Reload the job

		if ($job->get('bot_id') != $this->id) {
			// We didn't grab it in time.
			throw new Exception("Unable to lock job #{$job->id}");
		}

		$job->setStatus('taken');
		$job->set('taken_time', date('Y-m-d H:i:s'));
		$job->save();

		//do we need to slice this job?
		if (!$job->getFile()->isHydrated() && $can_slice) {
			//pull in our config and make sure it's legit.
			$config = $this->getSliceConfig();
			if (!$config->isHydrated()) {
				$job->setStatus('available');
				$job->set('bot_id', 0);
				$job->set('taken_time', 0);
				$job->save();

				throw new Exception("This bot does not have a slice engine + configuration set.");
			}

			//is there an existing slice job w/ this exact file and config?
			$sj = SliceJob::byConfigAndSource($config->id, $job->get('source_file_id'));
			if ($sj->isHydrated()) {
				//update our job status.
				$job->set('slice_job_id', $sj->id);
				$job->set('slice_complete_time', $job->get('taken_time'));
				$job->set('file_id', $sj->get('output_id'));
				$job->save();
			} else {
				//nope, create our slice job for processing.
				$sj->set('user_id', User::$me->id);
				$sj->set('job_id', $job->id);
				$sj->set('input_id', $job->get('source_file_id'));
				$sj->set('slice_config_id', $config->id);
				$sj->set('slice_config_snapshot', $config->getSnapshot());
				$sj->set('add_date', date("Y-m-d H:i:s"));
				$sj->setStatus('available');
				$sj->save();

				//update our job status.
				$job->setStatus('slicing');
				$job->set('slice_job_id', $sj->id);
				$job->save();
			}
		}

		$log = new JobClockEntry();
		$log->set('job_id', $job->id);
		$log->set('user_id', User::$me->id);
		$log->set('bot_id', $this->id);
		$log->set('queue_id', $job->get('queue_id'));
		$log->set('start_date', date("Y-m-d H:i:s"));
		$log->setStatus('working');
		$log->save();

		$this->set('job_id', $job->id);
		$this->setStatus(BotState::Working);
		$this->set('last_seen', date("Y-m-d H:i:s"));
		$this->save();

		return $job;
	}

	/**
	 * @param $job Job
	 * @return bool
	 */
	public function canDrop($job)
	{
		if ($job->get('bot_id') == $this->id && $this->get('job_id') == $job->id)
			return true;
		//if nobody has the job, we can safely drop it.  sometimes the web requests will time out and a bot will get stuck trying to drop a job.
		else if ($job->get('bot_id') == 0)
			return true;
		else
			return false;
	}

	/**
	 * @param $job Job
	 */
	public function dropJob($job)
	{
		//if its a sliced job, clear it for a potentially different bot.
		$job->reset();

		$log = $job->getLatestTimeLog();
		if ($log->isHydrated()) {
			$log->set('end_date', date("Y-m-d H:i:s"));
			$log->setStatus('dropped');
			$log->save();
		}

		$this->set('last_seen', date("Y-m-d H:i:s"));
		$this->reset();
	}

	public function pause()
	{
		$this->setStatus(BotState::Paused);
		$this->save();
	}

	public function unpause()
	{
		$this->setStatus(BotState::Working);
		$this->save();
	}

	/**
	 * @param $job Job
	 * @return bool
	 */
	public function canComplete($job)
	{
		if ($job->get('bot_id') == $this->id && $job->get('status') == 'taken')
			return true;
		//sometimes the web requests will time out and a bot will get stuck trying to complete a job.
		else if ($job->get('bot_id') == $this->id && $job->get('status') == 'qa')
			return true;
		else
			return false;
	}

	/**
	 * @param $job Job
	 */
	public function completeJob($job)
	{
		$job->complete();

		//copy our webcam image so that we stop overwriting the last image of the job.
		$webcam = $this->getWebcamImage();
		if ($webcam->isHydrated()) {
			$copy = $webcam->copy();
			$this->set('webcam_image_id', $copy->id);
		}

		$this->setStatus(BotState::Waiting);
		$this->set('last_seen', date("Y-m-d H:i:s"));
		$this->save();
	}

	public function getQueues()
	{
		$sql = "SELECT queue_id
				FROM bot_queues
				WHERE bot_id = ?
				ORDER BY priority ASC";
		$data = array($this->id);

		$queues = new Collection($sql, $data);
		$queues->bindType('queue_id', 'Queue');

		return $queues;
	}

	/**
	 * @param bool $can_slice
	 * @return Job
	 */
	public function findNewJob($can_slice = true) {
		$queues = $this->getQueues()->getAll();
		foreach($queues AS $row) {
			/** @var Queue $queue */
			$queue = $row['Queue'];
			$job = $queue->findNewJob($can_slice);
			if($job->isHydrated()) {
				return $job;
			}
		}
		return new Job();
	}

	public function delete()
	{
		//delete our jobs.
		$jobs = $this->getJobs()->getAll();
		foreach ($jobs AS $row) {
			/* @var $job Job */
			$job = $row['Job'];
			$job->delete();
		}

		$job_clocks = $this->getJobClocks()->getAll();
		foreach ($job_clocks AS $row) {
			/* @var $job_clock JobClockEntry */
			$job_clock = $row['JobClockEntry'];
			$job_clock->delete();
		}

		$sql = "delete from bot_queues where bot_id = ?";

		db()->execute($sql, array($this->id));

		parent::delete();
	}

	public function retire()
	{
		$this->setStatus(BotState::Retired);
		$this->save();
	}

	public function getSliceEngine()
	{
		return new SliceEngine($this->get('slice_engine_id'));
	}

	public function getSliceConfig()
	{
		return new SliceConfig($this->get('slice_config_id'));
	}

	public function reset()
	{
		$this->set('job_id', 0);
		$this->setStatus(BotState::Idle);
		$this->set('temperature_data', '');
		$this->save();
	}
}
