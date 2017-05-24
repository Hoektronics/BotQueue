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

class MainController extends Controller
{
	public function home()
	{
		if (User::isLoggedIn()) {
			$this->set('area', 'dashboard');
		}
	}

	public function dashboard()
	{
		if (!User::isLoggedIn()) {
			die('You must be logged in to view this page.');
		}

		//do we need to set a default?
		if (!User::$me->get('dashboard_style')) {
			User::$me->set('dashboard_style', 'large_thumbnails');
			User::$me->save();
		}

		//okay, pull in our dashboard style.
		$this->set('dashboard_style', User::$me->get('dashboard_style'));

		//are there any apps requesting access?
		$this->set('request_tokens', OAuthToken::getRequestTokensByIP()->getAll());

		$this->addTemplate(
			'bot_thumbnail_template',
			Controller::byName('bot')->renderTemplate('thumbnail')
		);

		$this->addTemplate(
			'bot_list_template',
			Controller::byName('bot')->renderTemplate('dashboard_list')
		);

		$this->addTemplate(
			'job_list_template',
			Controller::byName('job')->renderTemplate('job_list')
		);

		$this->addScript(
			'initial_data',
			"var initialData = " . Controller::byName('main')->renderView('dashboard_data'),
			"text/javascript"
		);

		$this->addScript("js/backbone.js");
	}

	public function dashboard_medium_thumbnails()
	{
		$this->setArg('bots');
	}
	public function dashboard_small_thumbnails()
	{
		$this->setArg('bots');
	}

	public function dashboard_data()
	{
		if (!User::isLoggedIn()) {
			die('You must be logged in to view this page.');
		}
		$content = array();
		$content['bots'] = array();

		$bots = User::$me->getActiveBots()->getAll();

		foreach ($bots AS $row) {
			/** @var Bot $bot */
			$bot = $row['Bot'];
			/** @var Job $job */
			$job = $row['Job'];
			$content['bots'][] = $this->_getBotData($bot, $job);
		}

		$on_deck = User::$me->getJobs(JobState::Available, 'user_sort', 'ASC');
		$content['on_deck'] = $this->_populateJobs($on_deck);

		$finished = User::$me->getJobs(JobState::Complete, 'verified_time', 'DESC');
		$content['finished'] = $this->_populateJobs($finished);

		$this->set('content', JSON::encode($content));
	}

	public function dashboard_style() {
		if(!User::$me->isLoggedIn())
			return;
		if($this->args('style')) {
			User::$me->set('dashboard_style', $this->args('style'));
			User::$me->save();
		}
	}

	public function activity()
	{
		$this->assertLoggedIn();

		$this->setTitle('Activity Log');

		$collection = User::$me->getActivityStream();

		$this->set('user', User::$me);
		$this->set('activities',
			$collection->getPage(
				$this->args('page'),
				20
			)
		);
	}

	public function draw_activities()
	{
		$this->setArg('user');
		$this->setArg('activities');
	}

	public function draw_error_log()
	{
		$this->setArg('errors');
		$this->setArg('hide');
	}

	public function shortcode()
	{
		$code = ShortCode::byCode($this->args('code'));

		die($code->get('url'));
	}

	public function tos()
	{
		$this->setTitle("Terms of Service");
	}

	public function privacy()
	{
		$this->setTitle("Privacy Policy");
	}

	public function stats()
	{
		$this->setTitle("Overall Stats");
		$this->set('area', 'stats');

		//active bots
		$sql = "SELECT count(id) FROM bots
        WHERE last_seen > DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
		$this->set('total_active_bots', db()->getValue($sql));

		//total prints
		$sql = "SELECT count(id) AS total FROM jobs WHERE status = 'available'";
		$this->set('total_pending_jobs', db()->getValue($sql));

		//total prints
		$sql = "SELECT count(id) AS total FROM jobs WHERE status = 'complete'";
		$this->set('total_completed_jobs', db()->getValue($sql));

		//total printing hours
		$sql = "SELECT CEIL(SUM(seconds)/3600) FROM stats";
		$totalHours = db()->getValue($sql);
		if ($totalHours != "")
			$this->set('total_printing_time', $totalHours);
		else
			$this->set('total_printing_time', 0);

		//user leader board - all time
		$sql = "
            SELECT CEIL(SUM(seconds)/3600) AS hours, user_id
            FROM stats
            GROUP BY user_id
            ORDER BY hours DESC
            LIMIT 10
        ";
		$this->set('user_leaderboard', db()->getArray($sql));

		//user leader board - last month
		$sql = "
            SELECT CEIL(SUM(seconds)/3600) AS hours, user_id
            FROM stats
            WHERE start_date > DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY user_id
            ORDER BY hours DESC
            LIMIT 10
        ";
		$this->set('user_leaderboard_30', db()->getArray($sql));

		//bot leader board - all time
		$sql = "
            SELECT CEIL(SUM(seconds)/3600) AS hours, bot_id
            FROM stats
            GROUP BY bot_id
            ORDER BY hours DESC
            LIMIT 10
        ";
		$this->set('bot_leaderboard', db()->getArray($sql));

		//bot leader board - all time
		$sql = "
            SELECT CEIL(SUM(seconds)/3600) AS hours, bot_id
            FROM stats
            WHERE start_date > DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY bot_id
            ORDER BY hours DESC
            LIMIT 10
        ";
		$this->set('bot_leaderboard_30', db()->getArray($sql));

		if (User::isLoggedIn()) {
			$me = array((int)User::$me->id);
			//active bots
			$sql = "SELECT count(id) FROM bots
            WHERE last_seen > DATE_SUB(NOW(), INTERVAL 5 MINUTE) AND user_id = ?";
			$this->set('my_total_active_bots', db()->getValue($sql, $me));

			//total prints
			$sql = "SELECT count(id) FROM jobs WHERE status = 'available' AND user_id = ?";
			$this->set('my_total_pending_jobs', db()->getValue($sql, $me));

			//total prints
			$sql = "SELECT count(id) AS total FROM jobs WHERE status = 'complete' AND user_id = ?";
			$this->set('my_total_completed_jobs', db()->getValue($sql, $me));

			//total printing hours
			$sql = "SELECT CEIL(SUM(seconds)/3600) FROM stats WHERE user_id = ?";
			$totalHours = db()->getValue($sql, $me);

			if ($totalHours != "")
				$this->set('my_total_printing_time', $totalHours);
			else
				$this->set('my_total_printing_time', 0);
		}
	}

	/**
	 * @param Bot $bot
	 * @return array
	 */
	private function _getStatusButtons($bot)
	{
		$buttons = array();

		$buttons['pause'] = array(
			"url" => $bot->getUrl() . "/pause",
			"icon" => "icon-pause",
			"text" => "pause job"
		);

		$buttons['dropjob'] = array(
			"url" => $bot->getUrl() . "/dropjob",
			"icon" => "icon-stop",
			"text" => "stop job"
		);

		$buttons['edit'] = array(
			"url" => $bot->getUrl() . "/edit",
			"icon" => "icon-cog",
			"text" => "edit bot"
		);

		$buttons['play'] = array(
			"url" => $bot->getUrl() . "/play",
			"icon" => "icon-play",
			"text" => "resume job"
		);

		$buttons['qa'] = array(
			"url" => $bot->getCurrentJob()->getUrl() . "/qa",
			"icon" => "icon-check",
			"text" => "verify output"
		);

		$buttons['offline'] = array(
			"url" => $bot->getUrl() . "/setstatus/offline",
			"icon" => "icon-stop",
			"text" => "take offline"
		);

		$buttons['online'] = array(
			"url" => $bot->getUrl() . "/setstatus/idle",
			"icon" => "icon-play",
			"text" => "bring online"
		);

		$buttons['retire'] = array(
			"url" => $bot->getUrl() . "/retire",
			"icon" => "icon-lock",
			"text" => "retire bot"
		);

		$buttons['delete'] = array(
			"url" => $bot->getUrl() . "/delete",
			"icon" => "icon-remove",
			"text" => "delete bot"
		);

		$buttons['error'] = array(
			"url" => $bot->getURL() . "/error",
			"icon" => "icon-exclamation-sign",
			"text" => "error mode"
		);

		return $buttons;
	}

	/**
	 * @param Bot $bot
	 * @param Job $job
	 * @return array
	 */
	private function _getBotData($bot, $job)
	{
		$botData = array();
		$botData['id'] = $bot->id;
		$botData['name'] = $bot->getName();
		$botData['status'] = $bot->getStatus();
		$botData['status_class'] = BotStatus::getStatusHTMLClass($bot);
		$botData['url'] = $bot->getUrl();
		$botData['last_seen'] = BotLastSeen::getHTML($bot);

		$webcam = $bot->getWebCamImage();
		if ($webcam->isHydrated()) {
			$botData['webcam_url'] = $webcam->getDownloadURL();
		} else {
			$botData['webcam_url'] = "/img/colorbars.gif";
		}

		$buttons = $this->_getStatusButtons($bot);
		$menu = array();
		$status = $bot->getStatus();
		if ($status == BotState::Working) {
			$menu[] = $buttons['pause'];
			$menu[] = $buttons['dropjob'];
			$menu[] = $buttons['edit'];
			$menu[] = $buttons['delete'];
		} else if ($status == BotState::Paused) {
			$menu[] = $buttons['play'];
			$menu[] = $buttons['dropjob'];
			$menu[] = $buttons['edit'];
			$menu[] = $buttons['delete'];
		} else if ($status == BotState::Slicing) {
			$menu[] = $buttons['dropjob'];
			$menu[] = $buttons['edit'];
			$menu[] = $buttons['delete'];
		} else if ($status == BotState::Waiting) {
			$menu[] = $buttons['qa'];
			$menu[] = $buttons['edit'];
			$menu[] = $buttons['delete'];
		} else if ($status == BotState::Idle) {
			$menu[] = $buttons['offline'];
			$menu[] = $buttons['edit'];
			$menu[] = $buttons['error'];
			$menu[] = $buttons['delete'];
		} else if ($status == BotState::Offline) {
			$menu[] = $buttons['online'];
			$menu[] = $buttons['edit'];
			$menu[] = $buttons['error'];
			$menu[] = $buttons['delete'];
			$menu[] = $buttons['retire'];
		} else if ($status == BotState::Maintenance) {
			$menu[] = $buttons['online'];
			$menu[] = $buttons['offline'];
			$menu[] = $buttons['edit'];
			$menu[] = $buttons['delete'];
		} else if ($status == BotState::Error) {
			$menu[] = $buttons['online'];
			$menu[] = $buttons['offline'];
			$menu[] = $buttons['edit'];
			$menu[] = $buttons['delete'];
		} else if ($status == BotState::Retired) {
			$menu[] = $buttons['delete'];
		}
		$botData['menu'] = $menu;

		if ($job->isHydrated()) {
			$jobData = array();
			$jobData['id'] = $job->id;
			$jobData['name'] = $job->getName();
			$jobData['url'] = $job->getUrl();
			$jobData['status'] = $job->get('status');
			$jobData['status_class'] = JobStatus::getStatusHTMLClass($job->get('status'));
			$jobData['elapsed'] = $job->getElapsedText();
			$jobData['estimated'] = $job->getEstimatedText();
			if ($job->get('status') == 'taken' || $job->get('status') == 'slicing') {
				$jobData['progress'] = round($job->get('progress'), 2);
				$jobData['bar_class'] = "";
			}

			$temps = JSON::decode($bot->get('temperature_data'));
			if ($bot->get('status') == BotState::Working && $temps !== NULL) {
				if (isset($temps->extruder))
					$botData['temp_extruder'] = $temps->extruder;
				if (isset($temps->bed))
					$botData['temp_bed'] = $temps->bed;
			}

			if ($job->get('status') == 'qa') {
				$jobData['qa_url'] = $job->getUrl() . "/qa";
			}

			$sliceJob = $job->getSliceJob();

			if ($job->get('status') == 'slicing' &&
				$sliceJob->get('status') == 'pending'
			) {
				$jobData['qa_url'] = $sliceJob->getUrl();
				$jobData['bar_class'] = "bar-warning";
				// Set it to 100% so it actually displays
				$jobData['progress'] = 100.00;
			}

			$botData['job'] = $jobData;
		}

		if ($bot->get('status') == 'error') {
			$botData['error_text'] = $bot->get('error_text');
			return $botData;
		}
		return $botData;
	}

	/**
	 * @param $jobs Collection
	 * @return array
	 */
	private function _populateJobs($jobs)
	{
		$content = array();

		$content['total'] = $jobs->count();
		$content['jobs'] = array();
		foreach ($jobs->getRange(0, 5) AS $row) {
			/** @var Job $job */
			$job = $row['Job'];
			$jobData = $this->_getJobData($job);
			if($job->get('status') == JobState::Available) {
				$jobData['managed_url'] = $job->getUrl() . "/cancel";
				$jobData['managed_icon'] = "icon-eject";
				$jobData['managed_text'] = "cancel";
			} else if($job->get('status') == JobState::Complete) {
				$jobData['managed_url'] = "/job/create/job:" . $job->id;
				$jobData['managed_icon'] = "icon-repeat";
				$jobData['managed_text'] = "re-run";
			}

			$content['jobs'][] = $jobData;
		}

		return $content;
	}

	/**
	 * @param Job $job
	 * @return array
	 */
	private function _getJobData($job)
	{
		$jobData = array();

		$jobData['id'] = $job->id;
		$jobData['name'] = $job->getName();
		$jobData['url'] = $job->getUrl();
		$jobData['elapsed'] = $job->getElapsedText();
		$jobData['queue_name'] = $job->getQueue()->getName();
		$jobData['queue_url'] = $job->getQueue()->getUrl();
		return $jobData;
	}
}
