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
  

class JobStatus {

    /**
     * @param $job Job
     * @return string
     */
    public static function getStatusHTML($job)
    {
        return "<span class=\"label " . JobStatus::getStatusHTMLClass($job->get('status')) . "\">" . $job->get('status') . "</span>";
    }

    public static function getStatusHTMLClass($status)
    {
        $s2c = array(
            'taken' => 'label-info',
            'qa' => 'label-warning',
            'slicing' => 'label-slicing',
            'complete' => 'label-success',
            'failure' => 'label-important',
            'canceled' => 'label-inverse'
        );

        return $s2c[$status];
    }

    public static function getStatsHtml($stats, $status)
    {
        $status_count = array_key_exists($status, $stats) ? $stats[$status] : 0;
        $status_pct = round(array_key_exists($status, $stats) ? $stats[$status . "_pct"] : 0, 2);
        return "<span class=\"label " . JobStatus::getStatusHTMLClass($status) . "\">"
            . $status_count
            . "</span>"
            . "(" . $status_pct . "%)";
    }

}