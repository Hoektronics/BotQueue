<?

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


class BotStats {

    /**
     * @param $bot
     * @return array
     */
    public static function getStats($bot)
    {
        $sql = "
				SELECT status, count(status) as cnt
				FROM jobs
				WHERE bot_id = " . db()->escape($bot->id) . "
				GROUP BY status
			";

        $data = array();
        $stats = db()->getArray($sql);
        if (!empty($stats)) {
            //load up our stats
            foreach ($stats AS $row) {
                $data[$row['status']] = $row['cnt'];
                $data['total'] += $row['cnt'];
            }

            //calculate percentages
            foreach ($stats AS $row)
                $data[$row['status'] . '_pct'] = ($row['cnt'] / $data['total']) * 100;
        }

        //pull in our time based stats.
        $sql = "
				SELECT sum(unix_timestamp(verified_time) - unix_timestamp(finished_time)) as wait, sum(unix_timestamp(finished_time) - unix_timestamp(taken_time)) as runtime, sum(unix_timestamp(verified_time) - unix_timestamp(taken_time)) as total
				FROM jobs
				WHERE status = 'complete'
					AND bot_id = " . db()->escape($bot->id);

        $stats = db()->getArray($sql);

        $data['total_waittime'] = (int)$stats[0]['wait'];
        $data['total_time'] = (int)$stats[0]['total'];

        //pull in our runtime stats
        $sql = "SELECT sum(unix_timestamp(end_date) - unix_timestamp(start_date)) FROM job_clock WHERE status != 'working' AND bot_id = " . db()->escape($bot->id);
        $data['total_runtime'] = (int)db()->getValue($sql);

        if ($data['total']) {
            $data['avg_waittime'] = $stats[0]['wait'] / $data['total'];
            $data['avg_runtime'] = $stats[0]['runtime'] / $data['total'];
            $data['avg_time'] = $stats[0]['total'] / $data['total'];
        } else {
            $data['avg_waittime'] = 0;
            $data['avg_runtime'] = 0;
            $data['avg_time'] = 0;
        }

        return $data;
    }
} 