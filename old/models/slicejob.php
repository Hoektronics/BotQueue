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

class SliceJob extends Model
{
	public function __construct($id = null)
	{
		parent::__construct($id, "slice_jobs");
	}

	public function getName()
	{
		return "#" . str_pad($this->id, 6, "0", STR_PAD_LEFT);
	}

	public function setStatus($status) {
		$this->set('status', $status);
	}

	public function getUrl()
	{
		return "/slicejob:" . $this->id;
	}

	public function getAPIData()
	{
		$r = array();
		$r['id'] = $this->id;
		$r['name'] = $this->getName();
		$r['input_file'] = $this->getInputFile()->getAPIData();
		$r['output_file'] = $this->getOutputFile()->getAPIData();
		$r['output_log'] = $this->get('output_log');
		$r['slice_config'] = $this->getSliceConfig()->getAPIData(true);
		$r['slice_config_snapshot'] = $this->get('slice_config_snapshot');
		$r['worker_token'] = $this->get('worker_token');
		$r['worker_name'] = $this->get('worker_name');
		$r['status'] = $this->get('status');
		$r['progress'] = $this->get('progress');
		$r['add_date'] = $this->get('add_date');
		$r['taken_date'] = $this->get('taken_date');
		$r['finish_date'] = $this->get('finish_date');

		return $r;
	}

	public function getUser()
	{
		return new User($this->get('user_id'));
	}

	public function getJob()
	{
		return new Job($this->get('job_id'));
	}

	public function getBot()
	{
		return $this->getJob()->getBot();
	}

	public function getInputFile()
	{
		return Storage::get($this->get('input_id'));
	}

	public function getOutputFile()
	{
		return Storage::get($this->get('output_id'));
	}

	public function getSliceConfig()
	{
		return new SliceConfig($this->get('slice_config_id'));
	}

	public function getSliceEngine()
	{
		return $this->getSliceConfig()->getEngine();
	}

	public function delete()
	{
		//todo: delete our files?
		//todo: change our status?

		parent::delete();
	}

	public function getStatusHTML()
	{
		return "<span class=\"label " . self::getStatusHTMLClass($this->get('status')) . "\">" . $this->get('status') . "</span>";
	}

	public static function getStatusHTMLClass($status)
	{
		$s2c = array(
			'available' => '',
			'slicing' => 'label-info',
			'pending' => 'label-warning',
			'complete' => 'label-success',
			'failure' => 'label-important',
			'expired' => 'label-inverse'
		);

		return $s2c[$status];
	}

	public function grab($uid)
	{
		if ($this->get('status') == 'available') {
			$this->setStatus('slicing');
			$this->set('taken_date', date('Y-m-d H:i:s'));
			$this->set('uid', $uid);
			$this->save();

            // Begin a transaction to avoid the race condition
            db()->beginTransaction();
			$sj = new SliceJob($this->id);
            
			if ($sj->get('uid') != $uid) {
                db()->rollBack(); // Nothing really to rollback
				throw new Exception("Unable to lock slice job #{$this->id}");
            }
            db()->commit();

			$bot = $this->getBot();
			$bot->setStatus(BotState::Slicing);
			$bot->save();
		}
	}

	public function fail()
	{
		$this->setStatus('failure');
		$this->save();

		$job = $this->getJob();
		$job->set('downloaded_time', date("Y-m-d H:i:s"));
		$job->set('finished_time', date("Y-m-d H:i:s"));
		$job->set('verified_time', date("Y-m-d H:i:s"));
		$job->setStatus('failure');
		$job->save();

		$bot = $this->getBot();
		$bot->reset();

		$log = new ErrorLog();
		$log->set('user_id', User::$me->id);
		$log->set('job_id', $job->id);
		$log->set('bot_id', $bot->id);
		$log->set('queue_id', $job->get('queue_id'));
		$log->set('reason', "Model slicing failed.");
		$log->set('error_date', date("Y-m-d H:i:s"));
		$log->save();
	}

	public function pass()
	{
		$this->setStatus('complete');
		$this->set('finish_date', date("Y-m-d H:i:s"));
		$this->save();

		$job = $this->getJob();
		$job->setStatus('taken');
		$job->save();

		$bot = $this->getBot();
		$bot->setStatus(BotState::Working);
		$bot->save();
	}

	public static function byConfigAndSource($config_id, $source_id)
	{
		$config_id = (int)$config_id;
		$source_id = (int)$source_id;

		$sql = "SELECT id
				FROM slice_jobs
				WHERE slice_config_id = ?
				AND input_id = ?
				AND user_id = ?
				AND status = 'complete'";

		$id = db()->getValue($sql, array($config_id, $source_id, User::$me->id));

		return new SliceJob($id);
	}

	public static function getJobsRequiringAction()
	{
		$sql = "SELECT id, input_id, job_id
		    	FROM slice_jobs
		    	WHERE status = 'pending'
		    	ORDER BY finish_date ASC";

		$jobs = new Collection($sql);
		$jobs->bindType('id', 'SliceJob');
		$jobs->bindType('input_id', STORAGE_METHOD);
		$jobs->bindType('job_id', 'Job');

		return $jobs;
	}
}