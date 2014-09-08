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

class Queue extends Model
{
    public function __construct($id = null)
    {
        parent::__construct($id, "queues");
    }

    public function getAPIData()
    {
        $d = array();
        $d['id'] = $this->id;
        $d['name'] = $this->getName();

        return $d;
    }

    public function isMine()
    {
        return (User::$me->id == $this->get('user_id'));
    }

    public function getName()
    {
        return $this->get('name');
    }

    public function getUser()
    {
        return new User($this->get('user_id'));
    }

    public function getUrl()
    {
        return "/queue:" . $this->id;
    }

    public function getJobs($status = null, $sortField = 'user_sort', $sortOrder = 'ASC')
    {
		$sql = "SELECT id FROM jobs WHERE queue_id = ? ";

		$data = array($this->id);

		if($status !== null) {
			$sql .= "AND status = ? ";
			$data[] = $status;
		}

		$sql .= "ORDER BY {$sortField} ". $sortOrder;

		$jobs = new Collection($sql, $data);
		$jobs->bindType('id', 'Job');

		return $jobs;
    }

    public function findNewJob($can_slice = true)
    {
		$data = array($this->id);

		$sql = "SELECT id FROM jobs WHERE queue_id = ? AND status = 'available' ";

		if(!$can_slice) {
			$sql .= "AND file_id > 0 ";
		}

		$sql .= "ORDER BY user_sort ASC";

        $job_id = db()->getValue($sql, $data);

        return new Job($job_id);
    }

    public function getActiveJobs($sortField = 'user_sort', $sortOrder = 'ASC')
    {
        $sql = "SELECT id
				FROM jobs
				WHERE queue_id = ?
				AND status IN ('available', 'taken')
				ORDER BY {$sortField} ". $sortOrder;

		$jobs = new Collection($sql, array($this->id));
		$jobs->bindType('id', 'Job');

		return $jobs;
    }

    public function getBots()
    {
        $sql = "SELECT id
				FROM bots
				WHERE queue_id = ?
				ORDER BY last_seen DESC";

		$bots = new Collection($sql, array($this->id));
		$bots->bindType('id', 'Bot');

		return $bots;
    }

    /**
     * @param $file StorageInterface
     * @param int $qty
     * @return array
     * @throws Exception
     */
    public function addFile($file, $qty = 1)
    {
        if ($file->isKnownType()) {
            $jobs = array();

            for ($i = 0; $i < $qty; $i++) {
                $jobs[] = Job::addFileToQueue($this->id, $file);
            }

            return $jobs;
        }
        else
            throw new Exception("Unknown file type");
    }

    public function getErrorLog()
    {
        $sql = "SELECT id
				FROM error_log
				WHERE queue_id = ?
				ORDER BY error_date DESC";

		$logs = new Collection($sql, array($this->id));
		$logs->bindType('id', 'ErrorLog');

		return $logs;
    }

    public function flush()
    {
        $sql = "DELETE FROM jobs
				WHERE queue_id = ?
				AND status = 'available'";
        db()->execute($sql, array($this->id));
    }

    public function delete()
    {
        $sql = "DELETE FROM jobs
				WHERE queue_id = ?";

        db()->execute($sql, array($this->id));

        $sql = "DELETE FROM error_log
				WHERE queue_id = ?";
        db()->execute($sql, array($this->id));

        parent::delete();
    }
}