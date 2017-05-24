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

class BotController extends Controller
{
	public static $failure_options = array(
		"Unknown" => "Unknown Failure",
		"Extruder Jam" => "Extruder Jam (Stopped extrusion, filament stripped, etc.)",
		"XY Offset" => "XY Layers Offset (Motors skipping, etc.)",
		"Print Dislodged" => "Print dislodged from build platform",
		"Machine Frozen" => "Machine frozen and not responding (software crash, etc.)",
		"Out of Filament" => "Ran out of filament, print did not complete.",
		"Poor Quality" => "Poor print quality (blobbing, loose threads, etc.)",
		"Other" => "Other - Please enter reason in field below."
	);

	public function home()
	{
		$this->assertLoggedIn();

		$this->setTitle(User::$me->getName() . "'s Bots");
		$this->set('area', 'bots');

		$collection = User::$me->getBots();

		$this->set('bots',
			$collection->getPage(
				$this->args('page'),
				20
			)
		);
	}

	public function register()
	{
		$this->assertLoggedIn();

		$this->setTitle('Register a new Bot');
		$this->set('area', 'bots');

		$bot = new Bot();

		//load up our form.
		$form = $this->_createBotRegisterForm($bot);

		//handle our form
		if ($form->checkSubmitAndValidate($this->args())) {
			$bot->set('user_id', User::$me->id);
			$bot->set('name', $form->data('name'));
			$bot->set('manufacturer', $form->data('manufacturer'));
			$bot->set('model', $form->data('model'));
			$bot->setStatus(BotState::Offline);
			$bot->save();

			Activity::log("registered the bot " . $bot->getLink() . ".");

			$this->forwardToUrl($bot->getUrl() . "/edit/setup");
		}

		$this->set('form', $form);
	}

	/**
	 * @param $bot Bot is the selected bot
	 * @return Form the form we return
	 */
	private function _createBotRegisterForm($bot)
	{

		$form = new Form('register');
		$form->action = "/bot/register";

		$form->add(
			TextField::name('name')
				->label('Bot Name')
				->help('What should humans call your bot?')
				->required(true)
				->value($bot->get('name'))
		);

		$form->add(
			TextField::name('manufacturer')
				->label('Manufacturer')
				->help('Which company (or person) built your bot?')
				->required(true)
				->value($bot->get('manufacturer'))
		);

		$form->add(
			TextField::name('model')
				->label('Model')
				->help('What is the model or name of your bot design?')
				->required(true)
				->value($bot->get('model'))
		);

		return $form;
	}

	public function view()
	{
		$this->assertLoggedIn();
		$this->set('area', 'bots');

		try {
			//how do we find them?
			if ($this->args('id'))
				$bot = new Bot($this->args('id'));
			else
				throw new Exception("This shouldn't happen");

			//did we really get someone?
			if (!$bot->isHydrated())
				throw new Exception("Could not find that bot.");
			if (!$bot->isMine())
				throw new Exception("You cannot view that bot.");

			$this->setTitle("View Bot - " . $bot->getName());

			$this->set('bot', $bot);
			$this->set('queue', $bot->getQueues()->getAll());
			$this->set('job', $bot->getCurrentJob());
			$this->set('engine', $bot->getSliceEngine());
			$this->set('config', $bot->getSliceConfig());
			$this->set('webcam', $bot->getWebcamImage());
			$this->set('app', $bot->getApp());

			$jobs = $bot->getJobs(null, 'user_sort', 'DESC');
			$this->set('jobs', $jobs->getRange(0, 50));
			$this->set('job_count', $jobs->count());
			$this->set('stats', BotStats::getStats($bot));
			$this->set('owner', $bot->getUser());
			$this->set('errors', $bot->getErrorLog()->getRange(0, 50));
		} catch (Exception $e) {
			$this->set('megaerror', $e->getMessage());
			$this->setTitle("View Bot - Error");
		}
	}

	//TODO: convert to AJAX
	public function set_status()
	{
		$this->assertLoggedIn();

		try {
			//how do we find them?
			if ($this->args('id'))
				$bot = new Bot($this->args('id'));
			else
				throw new Exception("This shouldn't happen");

			//did we really get someone?
			if (!$bot->isHydrated())
				throw new Exception("Could not find that bot.");

			$status = $this->args('status');

			if (!$bot->isMine())
				throw new Exception("You cannot view that bot.");
			if ($bot->get('status') == BotState::Retired)
				throw new Exception("This bot is retired. You can't change it's status");
			if (($bot->get('status') == BotState::Working || $bot->get('status') == BotState::Slicing) &&
				$status == BotState::Offline
			)
				throw new Exception("You cannot take a working bot offline through the web interface.  You must stop the job from the client first.");

			if ($status == BotState::Offline)
				Activity::log("took the bot " . $bot->getLink() . " offline.");
			else if ($status == BotState::Idle)
				Activity::log("brought the bot " . $bot->getLink() . " online.");

			//do we need to drop a job?
			$job = $bot->getCurrentJob();
			if ($job->isHydrated())
				$bot->dropJob($job);

			//save it and clear out some junk
			$bot->set('temperature_data', '');
			$bot->set('error_text', '');
			$bot->setStatus($status);
			$bot->save();

			$this->forwardToUrl("/");
		} catch (Exception $e) {
			$this->set('megaerror', $e->getMessage());
			$this->setTitle("Change Bot Status - Error");
		}
	}

	public function draw_bots()
	{
		$this->setArg('bots');
	}

	public function draw_bots_small()
	{
		$this->setArg('bots');
	}

	public function edit()
	{
		$this->assertLoggedIn();
		$this->set('area', 'bots');

		try {
			//how do we find them?
			if ($this->args('id'))
				$bot = new Bot($this->args('id'));
			else
				throw new Exception("Could not find that bot.");

			//did we really get someone?
			if (!$bot->isHydrated())
				throw new Exception("Could not find that bot.");
			if (!$bot->isMine())
				throw new Exception("You cannot view that bot.");

			$this->setTitle('Edit Bot - ' . $bot->getName());

			$wizard = new Wizard('bot', $this->args());

			if (!$this->args('setup')) {
				$wizard->disableWizardMode();
			}

			// Create our forms
			$infoForm = $this->_createInfoForm($bot);
			$slicingForm = $this->_createSlicingForm($bot);
			$driverForm = $this->_createDriverForm($bot);

			// Add them to the wizard
			$wizard->add("Information / Details", $infoForm);
			$wizard->add("Slicing Setup", $slicingForm);
			$wizard->add("Driver Configuration", $driverForm);

			//handle our forms
			if ($infoForm->checkSubmitAndValidate($this->args())) {
				$bot->set('name', $infoForm->data('name'));
				$bot->set('manufacturer', $infoForm->data('manufacturer'));
				$bot->set('model', $infoForm->data('model'));
				$bot->set('electronics', $infoForm->data('electronics'));
				$bot->set('firmware', $infoForm->data('firmware'));
				$bot->set('extruder', $infoForm->data('extruder'));
				$bot->save();

				Activity::log("edited the information for bot " . $bot->getLink() . ".");
			} else if ($slicingForm->checkSubmitAndValidate($this->args())) {
				$bot->set('slice_engine_id', $slicingForm->data('slice_engine_id'));
				$bot->set('slice_config_id', $slicingForm->data('slice_config_id'));

				$config = $bot->getDriverConfig();
				$config->can_slice = (bool)$slicingForm->data('can_slice');
				$bot->set('driver_config', json::encode($config));

				$bot->save();

				Activity::log("edited the slicing info for bot " . $bot->getLink() . ".");

			} else if ($driverForm->checkSubmitAndValidate($this->args())) {
				$bot->set('oauth_token_id', $driverForm->data('oauth_token_id'));
				$bot->set('driver_name', $driverForm->data('driver_name'));

				//create and save our config
				$config = $bot->getDriverConfig();

				$config->driver = $bot->get('driver_name');
				if ($bot->get('driver_name') == 'dummy') {
					if ($this->args('delay'))
						$config->delay = $this->args('delay');
				} elseif ($bot->get('driver_name') == 'printcore' ||
					$bot->get('driver_name') == 's3g'
				) {
					$config->port = $this->args('serial_port');
					$config->port_id = $this->args('port_id');
					$config->baud = $this->args('baudrate');
				}

				//did we get webcam info?
				if ($this->args('webcam_device')) {
					if (!isset($config->webcam))
						$config->webcam = new stdClass();
					$config->webcam->device = $this->args('webcam_device');
					if ($this->args('webcam_id'))
						$config->webcam->id = $this->args('webcam_id');
					if ($this->args('webcam_name'))
						$config->webcam->name = $this->args('webcam_name');
					if ($this->args('webcam_brightness'))
						$config->webcam->brightness = (int)$this->args('webcam_brightness');
					if ($this->args('webcam_contrast'))
						$config->webcam->contrast = (int)$this->args('webcam_contrast');
				} else
					unset($config->webcam);

				//save it all to the bot as json.
				$bot->set('driver_config', json::encode($config));
				$bot->save();

				Activity::log("edited the driver configuration for bot " . $bot->getLink() . ".");
			}

			if ($wizard->isFinished()) {
				$this->forwardToURL($bot->getUrl());
			}

			$this->set('bot_id', $bot->id);
			$this->set('wizard', $wizard);
		} catch (Exception $e) {
			$this->set('megaerror', $e->getMessage());
			$this->setTitle("Bot Edit - Error");
		}
	}

	public function error()
	{
		$this->assertLoggedIn();

		try {
			//how do we find them?
			if ($this->args('id'))
				$bot = new Bot($this->args('id'));
			else
				throw new Exception("This shouldn't happen");

			//did we really get someone?
			if (!$bot->isHydrated())
				throw new Exception("Could not find that bot.");

			if (!$bot->isMine())
				throw new Exception("You cannot view that bot.");

			$this->setTitle('Error mode - ' . $bot->getName());

			//load up our form.
			$form = $this->_createErrorForm($bot);
			$form->action = $bot->getUrl() . "/error";

			//handle our form
			if ($form->checkSubmitAndValidate($this->args())) {
				if ($form->data('failure_reason') == 'Other')
					$error_text = $form->data('failure_reason_other');
				else
					$error_text = $form->data('failure_reason');

				$bot->set('error_text', $error_text);
				$bot->setStatus(BotState::Error);
				$bot->save();

				$this->forwardToUrl("/");
			}

			$this->set('form', $form);
		} catch (Exception $e) {
			$this->set('megaerror', $e->getMessage());
			$this->setTitle("Error setting bot status to Error");
		}
	}

	public function dropjob()
	{
		$this->assertLoggedIn();

		try {
			//how do we find them?
			if ($this->args('id'))
				$bot = new Bot($this->args('id'));
			else
				throw new Exception("This shouldn't happen");

			//did we really get someone?
			if (!$bot->isHydrated())
				throw new Exception("Could not find that bot.");
			if (!$bot->isMine())
				throw new Exception("You cannot view that bot.");
			if (!($bot->get('status') == 'slicing' || $bot->get('status') == 'working' || $bot->get('status') == 'paused'))
				throw new Exception("Bot must be slicing, working, or paused to drop a job.");
			$job = $bot->getCurrentJob();
			if (!$job->isHydrated())
				throw new Exception("Job must be a real job.");
			if (!$bot->canDrop($job))
				throw new Exception("Job cannot be dropped.");

			$this->setTitle('Drop Job - ' . $bot->getName() . " - " . $job->getName());

			//load up our form.
			$form = $this->_createJobDropForm($bot, $job);
			$form->action = $bot->getUrl() . "/dropjob";

			//handle our form
			if ($form->checkSubmitAndValidate($this->args())) {
				$bot->dropJob($job);

				//do we want to cancel the job?
				if ($form->data('cancel_job'))
					$job->cancelJob();

				//do we want to go offline?
				if ($form->data('take_offline')) {
					$bot->setStatus(BotState::Offline);
					$bot->save();
				}

				//was there a job error?
				if ($form->data('job_error')) {
					if ($form->data('failure_reason') == 'Other')
						$error_text = $form->data('failure_reason_other');
					else
						$error_text = $form->data('failure_reason');

					$job->logError($error_text);
				}

				Activity::log("dropped the job " . $job->getLink() . ".");

				$this->forwardToUrl($bot->getUrl());
			}

			$this->set('form', $form);
		} catch (Exception $e) {
			$this->set('megaerror', $e->getMessage());
			$this->setTitle("Bot Drop Job - Error");
		}
	}

	public function pause()
	{
		$this->assertLoggedIn();

		try {
			//how do we find them?
			if ($this->args('id'))
				$bot = new Bot($this->args('id'));
			else
				throw new Exception("Could not find that bot.");

			//did we really get someone?
			if (!$bot->isHydrated())
				throw new Exception("Could not find that bot.");
			if (!$bot->isMine())
				throw new Exception("You cannot view that bot.");
			if ($bot->get('status') != 'working')
				throw new Exception("Bot must be working to pause a job.");
			$job = $bot->getCurrentJob();
			if (!$job->isHydrated())
				throw new Exception("Job must be a real job.");
			if (!$bot->canDrop($job))
				throw new Exception("Job cannot be dropped.");

			//okay, pause it.
			$bot->pause();

			Activity::log("paused the bot " . $bot->getLink() . ".");

			$this->forwardToUrl("/");
		} catch (Exception $e) {
			$this->set('megaerror', $e->getMessage());
			$this->setTitle("Bot Pause Job - Error");
		}
	}

	public function play()
	{
		$this->assertLoggedIn();

		try {
			//how do we find them?
			if ($this->args('id'))
				$bot = new Bot($this->args('id'));
			else
				throw new Exception("Could not find that bot.");

			//did we really get someone?
			if (!$bot->isHydrated())
				throw new Exception("Could not find that bot.");
			if (!$bot->isMine())
				throw new Exception("You cannot view that bot.");
			if ($bot->get('status') != 'paused')
				throw new Exception("Bot must be paused to unpause a job.");
			$job = $bot->getCurrentJob();
			if (!$job->isHydrated())
				throw new Exception("Job must be a real job.");
			if (!$bot->canDrop($job))
				throw new Exception("Job cannot be dropped.");

			//okay, unpause it.
			$bot->unpause();

			Activity::log("unpaused the bot " . $bot->getLink() . ".");

			$this->forwardToUrl("/");
		} catch (Exception $e) {
			$this->set('megaerror', $e->getMessage());
			$this->setTitle("Bot Unpause Job - Error");
		}
	}

	/**
	 * @param $bot Bot is the selected bot
	 * @param $job Job is the selected job
	 * @return Form the form we return
	 */
	public function _createJobDropForm($bot, $job)
	{
		$form = new Form();

		$form->add(
			DisplayField::name('bot')
				->label('Bot Name')
				->value($bot->getLink())
		);

		$form->add(
			DisplayField::name('job')
				->label('Job Name')
				->value($job->getLink())
		);

		$form->add(
			CheckboxField::name('take_offline')
				->label('Take Offline')
				->help('Should the bot be taken offline afterwards?')
				->value(false)
		);

		$form->add(
			CheckboxField::name('cancel_job')
				->label('Cancel Job')
				->help('Do you want to cancel this job?')
				->value(false)
		);

		$form->add(
			CheckboxField::name('job_error')
				->label('Job/Bot Error')
				->help('Where there errors with the job or bot?')
				->value(false)
		);

		$form->add(
			SelectField::name('failure_reason')
				->label('Reason for failure')
				->help('Please enter a reason for rejecting this print.')
				->required(true)
				->options(self::$failure_options)
		);

		$form->add(
			TextField::name('failure_reason_other')
				->label('Other Reason')
				->help('If you selected "other" above, please enter the reason here.')
				->required(false)
				->value("")
		);

		return $form;
	}

	/**
	 * @param $bot Bot
	 * @return Form the form we return
	 */
	public function _createErrorForm($bot)
	{
		$form = new Form();

		$form->add(
			DisplayField::name('bot')
				->label('Bot Name')
				->value($bot->getLink())
		);

		$form->add(
			SelectField::name('failure_reason')
				->label('Reason for failure')
				->help('Please enter a reason for rejecting this print.')
				->required(true)
				->options(self::$failure_options)
		);

		$form->add(
			TextField::name('failure_reason_other')
				->label('Other Reason')
				->help('If you selected "other" above, please enter the reason here.')
				->required(false)
				->value("")
		);

		return $form;
	}

	public function retire()
	{
		$this->assertLoggedIn();
		$this->set('area', 'bots');

		try {
			//how do we find them?
			if ($this->args('id'))
				$bot = new Bot($this->args('id'));
			else
				throw new Exception("Could not find that bot.");

			//did we really get someone?
			if (!$bot->isHydrated())
				throw new Exception("Could not find that bot.");
			if ($bot->get('user_id') != User::$me->id)
				throw new Exception("You do not own this bot.");
			if ($bot->get('status') != 'offline')
				throw new Exception("You cannot retire bots that are not offline. Please take the bot offline first.");

			$this->set('bot', $bot);
			$this->setTitle('Retire Bot - ' . $bot->getName());

			if ($this->args('submit')) {
				Activity::log("retired the bot <strong>" . $bot->getName() . "</strong>. RIP");

				$bot->retire();

				$this->forwardToUrl("/bots");
			}
		} catch (Exception $e) {
			$this->setTitle('Retire Bot - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function delete()
	{
		$this->assertLoggedIn();
		$this->set('area', 'bots');

		try {
			//how do we find them?
			if ($this->args('id'))
				$bot = new Bot($this->args('id'));
			else
				throw new Exception("Could not find that bot.");

			//did we really get someone?
			if (!$bot->isHydrated())
				throw new Exception("Could not find that bot.");
			if ($bot->get('user_id') != User::$me->id)
				throw new Exception("You do not own this bot.");
			if ($bot->get('status') == 'working' || $bot->get('status') == 'paused')
				throw new Exception("You cannot delete bots that are currently working.  First, use the client software to cancel the job and then delete the bot.");

			$this->set('bot', $bot);
			$this->setTitle('Delete Bot - ' . $bot->getName());

			if ($this->args('submit')) {
				Activity::log("deleted the bot <strong>" . $bot->getName() . "</strong>.");

				$bot->delete();

				$this->forwardToUrl("/bots");
			}
		} catch (Exception $e) {
			$this->setTitle('Delete Bot - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function listjobs()
	{
		$this->assertLoggedIn();

		try {
			//how do we find them?
			if ($this->args('id'))
				$bot = new Bot($this->args('id'));
			else
				throw new Exception("Could not find that bot.");

			//did we really get someone?
			if (!$bot->isHydrated())
				throw new Exception("Could not find that bot.");
			if (!$bot->isMine())
				throw new Exception("You cannot view that bot.");
			$this->set('bot', $bot);

			$this->setTitle("Bot Jobs - " . $bot->getName());

			$collection = $bot->getJobs();

			$this->set('jobs',
				$collection->getPage(
					$this->args('page'),
					20
				)
			);
		} catch (Exception $e) {
			$this->set('megaerror', $e->getMessage());
			$this->setTitle("View Bot - Error");
		}
	}

	/**
	 * @param $bot Bot is the selected bot
	 * @return Form the form we return
	 */
	private function _createInfoForm($bot)
	{

		$form = new Form('info');
		if ($this->args('setup')) {
			$form->action = $bot->getUrl() . "/edit/setup";
		} else {
			$form->action = $bot->getUrl() . "/edit";
		}

		$form->add(
			DisplayField::name('title')
				->label('')
				->value('<h2>Information / Details</h2>')
		);

		$form->add(
			TextField::name('name')
				->label('Bot Name')
				->help('What should humans call your bot?')
				->required(true)
				->value($bot->get('name'))
		);

		$form->add(
			TextField::name('manufacturer')
				->label('Manufacturer')
				->help('Which company (or person) built your bot?')
				->required(true)
				->value($bot->get('manufacturer'))
		);

		$form->add(
			TextField::name('model')
				->label('Model')
				->help('What is the model or name of your bot design?')
				->required(true)
				->value($bot->get('model'))
		);

		$form->add(
			TextField::name('electronics')
				->label('Electronics')
				->help('What electronics are you using to control your bot?')
				->value($bot->get('electronics'))
		);

		$form->add(
			TextField::name('firmware')
				->label('Firmware')
				->help('What firmware are you running on your electronics?')
				->value($bot->get('firmware'))
		);

		$form->add(
			TextField::name('extruder')
				->label('Extruder')
				->help('What extruder are you using to print with?')
				->value($bot->get('extruder'))
		);

		return $form;
	}

	/**
	 * @param $bot Bot is the selected bot
	 * @return form Form the form we return
	 */
	private function _createSlicingForm($bot)
	{
		//load up our engines.
		if (User::isAdmin())
			$allEngines = SliceEngine::getAllEngines()->getAll();
		else
			$allEngines = SliceEngine::getPublicEngines()->getAll();

		$engineList[0] = "None";
		foreach ($allEngines AS $row) {
			/* @var $e SliceEngine */
			$e = $row['SliceEngine'];
			$engineList[$e->id] = $e->getName();
		}

		//load up our configs
		$engine = $bot->getSliceEngine();
		//       if (User::isAdmin())
		//   $configs = $engine->getAllConfigs()->getAll();
		// else
		$myConfigs = $engine->getMyConfigs()->getAll();

		$configList = array();
		if (!empty($myConfigs)) {
			foreach ($myConfigs AS $row) {
				/* @var $c SliceConfig */
				$c = $row['SliceConfig'];
				$configList[$c->id] = $c->getName();
			}
		} else
			$configList[0] = "None";

		$form = new Form('slicing');
		if ($this->args('setup')) {
			$form->action = $bot->getUrl() . "/edit/setup";
		} else {
			$form->action = $bot->getUrl() . "/edit";
		}

		$form->add(
			DisplayField::name('title')
				->label('')
				->value('<h2>Slicing Setup</h2>')
		);

		//load up our queues.
		$queues = User::$me->getQueues()->getAll();
		$queueList = array();
		foreach ($queues AS $row) {
			/* @var $q Queue */
			$q = $row['Queue'];
			$queueList[$q->id] = $q->getName();
		}

		$config = $bot->getDriverConfig();

		$form->add(
			CheckboxField::name('can_slice')
				->label('Client Slicing Enabled?')
				->help('Is the controlling computer fast enough to slice?')
				->value($config->can_slice)
		);

		$form->add(
			SelectField::name('queue_id')
				->label('Queue')
				->help('Which queue does this bot pull jobs from?')
				->required(true)
				->value($bot->get('queue_id'))
				->options($queueList)
		);

		$form->add(
			SelectField::name('slice_engine_id')
				->id('slice_engine_dropdown')
				->label('Slice Engine')
				->help('Which slicing engine does this bot use?')
				->value($bot->get('slice_engine_id'))
				->options($engineList)
				->onchange('update_slice_config_dropdown(this)')
		);

		$form->add(
			SelectField::name('slice_config_id')
				->id('slice_config_dropdown')
				->label('Slice Configuration')
				->help('Which slicing configuration to use? <a href="/slicers">click here</a> to view/edit configs.')
				->value($bot->get('slice_config_id'))
				->options($configList)
		);

		return $form;
	}

	public function slice_config_select()
	{
		//load up our configs
		$engine = new SliceEngine($this->args('id'));
		$configs = $engine->getMyConfigs()->getAll();
		$configList = array();
		if (!empty($configs)) {
			foreach ($configs AS $row) {
				/* @var $c SliceConfig */
				$c = $row['SliceConfig'];
				$configList[$c->id] = $c->getName();
			}
		} else
			$configList[0] = "None";

		foreach ($configList as $id => $name)
			echo '<option value="' . $id . '">' . $name . '</option>' . "\n";

		exit;
	}

	/**
	 * @param $bot Bot is the selected bot
	 * @return form Form the form we return
	 */
	private function _createDriverForm($bot)
	{
		$form = new Form('driver');
		$form->action = $bot->getUrl() . "/edit";

		switch ($bot->getStatus()) {
			case BotState::Idle:
			case BotState::Offline:
			case BotState::Error:
			case BotState::Waiting:
				break; // We're okay to edit with these states
			default:
				$form->add(
					ErrorField::name('error')
						->value("The bot must be in an idle, offline, error, or waiting state in order to edit the driver config.")
				);
				return $form;
		}
		//load up our apps.
		$authorized = User::$me->getAuthorizedApps()->getAll();
		$apps[0] = "None";
		foreach ($authorized AS $row) {
			/* @var $e OAuthToken */
			$e = $row['OAuthToken'];
			$apps[$e->id] = $e->getName();
		}

		// load up our drivers
		$drivers = array(
			'printcore' => 'RepRap Serial Driver',
			'dummy' => 'Dummy Driver',
			's3g' => 'Makerbot Driver (Experimental)'
		);

		$form->add(
			DisplayField::name('title')
				->label('')
				->value("<h2>Driver Configuration</h2>")
		);

		$form->add(
			SelectField::name('oauth_token_id')
				->id('oauth_token_dropdown')
				->label('Computer')
				->help('Which computer is this bot connected to? <a href="/apps">Full list in the apps area.</a>')
				->value($bot->get('oauth_token_id'))
				->options($apps)
				->onchange('update_driver_form(this)')
		);

		$form->add(
			SelectField::name('driver_name')
				->id('driver_name_dropdown')
				->label('Driver Name')
				->help('Which driver to use? <a href="/help">More info available in the help area.</a>')
				->required(true)
				->value($bot->get('driver_name'))
				->options($drivers)
				->onchange('update_driver_form(this)')
		);

		$form->add(
			RawField::name("driver_edit_area")
				->value('<div id="driver_edit_area"></div>')
		);

		return $form;
	}

	public function driver_form()
	{
		try {
			//load our bot
			$bot = new Bot($this->args('id'));
			if (!$bot->isHydrated())
				throw new Exception("Could not find that bot.");
			if (!$bot->isMine())
				throw new Exception("You cannot view that bot.");

			if ($this->args('token_id') == 0) {
				$this->set('nodriver', "No driver was selected");
			} else {

				//load our token
				$token = new OAuthToken($this->args('token_id'));

				if (!$token->isHydrated())
					throw new Exception("Could not find that computer.");
				if (!$token->isMine())
					throw new Exception("This is not your computer.");

				//what driver form to create?
				$driver = $this->args('driver');

				//pass on our info.
				$this->set('bot', $bot);
				$this->set('driver', $driver);
				$this->set('token', $token);
				$devices = json::decode($token->get('device_data'));
				$this->set('devices', $devices);

				//pull in our driver config
				$driver_config = $bot->getDriverConfig();

				//if we're using the same driver, pull in old values...
				if ($driver == $bot->get('driver_name')) {

					$this->set('driver_config', $driver_config);

					if (is_object($driver_config)) {
						$this->set('delay', $driver_config->delay);
						$this->set('serial_port', $driver_config->port);
						$this->set('baudrate', $driver_config->baud);
					}
				} else if ($driver == "dummy") {
					$this->set('delay', '0.001');
				}

				//pull in our old webcam values too.
				if (is_object($driver_config) && !empty($driver_config->webcam)) {
					$this->set('webcam_id', $driver_config->webcam->id);
					$this->set('webcam_name', $driver_config->webcam->name);
					$this->set('webcam_device', $driver_config->webcam->device);
					$this->set('webcam_brightness', $driver_config->webcam->brightness);
					$this->set('webcam_contrast', $driver_config->webcam->contrast);
				} //okay, no webcam settings.
				else {
					//some default webcam settings.
					$this->set('webcam_id', '');
					$this->set('webcam_name', '');
					$this->set('webcam_device', '');
					$this->set('webcam_brightness', 50);
					$this->set('webcam_contrast', 50);
				}

				$this->set('driver_config', $driver_config);

				$this->set('baudrates', array(
					250000,
					115200,
					57600,
					38400,
					28880,
					19200,
					14400,
					9600
				));
			}
		} catch (Exception $e) {
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function statusbutton()
	{
		$bot = $this->args('bot');
		$this->set('bot', $bot);
	}

	public function thumbnail()
	{
	} // Template

	public function dashboard_list()
	{
	} // Template

	public function live()
	{
		$this->assertAdmin();

		$this->setTitle("Live Bots View");

		$sql = "SELECT id, job_id FROM bots WHERE webcam_image_id != 0 AND last_seen > NOW() - 3600 ORDER BY last_seen DESC";
		$bots = new Collection($sql);
		$bots->bindType('id', 'Bot');
		$bots->bindType('job_id', 'Job');

		$this->set('bots', $bots->getAll());
		$this->set('dashboard_style', 'medium_thumbnails');
	}
}