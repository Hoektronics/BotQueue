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
  

class QueueStats {

    /**
     * @param $queue Queue
     * @return array
     */
    public static function getStats($queue)
    {
        $sql = "SELECT status, count(status) as cnt
				FROM jobs
				WHERE status != 'canceled' and
				queue_id = ?
				GROUP BY status";

		$stats = db()->getArray($sql, array($queue->id));
        $data = array();
        if (!empty($stats)) {
            //load up our stats
            foreach ($stats AS $row) {
                // Cancelled jobs don't count
                if ($row['status'] != 'canceled') {
                    $data[$row['status']] = $row['cnt'];
                    $data['total'] += $row['cnt'];
                }
            }

            //calculate percentages
            foreach ($stats AS $row)
                $data[$row['status'] . '_pct'] = ($row['cnt'] / $data['total']) * 100;
        }

        //pull in our time based stats.
        $sql = "SELECT sum(unix_timestamp(taken_time) - unix_timestamp(created_time)) as wait, sum(unix_timestamp(finished_time) - unix_timestamp(taken_time)) as runtime, sum(unix_timestamp(finished_time) - unix_timestamp(created_time)) as total
				FROM jobs
				WHERE status = 'complete'
				AND queue_id = ?";

        $stats = db()->getArray($sql, array($queue->id));
        $data['total_waittime'] = (int)$stats[0]['wait'];
        $data['total_time'] = (int)$stats[0]['total'];

        //pull in our runtime stats
        $sql = "SELECT sum(unix_timestamp(end_date) - unix_timestamp(start_date)) FROM job_clock WHERE status != 'working' AND queue_id = ?";
        $data['total_runtime'] = (int)db()->getValue($sql, array($queue->id));

        if ($data['total'] > 0) {
            $data['avg_waittime'] = $data['total_waittime'] / $data['total'];
            $data['avg_runtime'] = $data['total_runtime'] / $data['total'];
            $data['avg_time'] = $data['total_time'] / $data['total'];
        } else {
            $data['avg_waittime'] = 0;
            $data['avg_runtime'] = 0;
            $data['avg_time'] = 0;
        }

        return $data;
    }
}