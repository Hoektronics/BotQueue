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

class Bot_EditController extends Controller
{
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

			if(!$this->args('setup')) {
				$wizard->disableWizardMode();
			}

			// Create our forms
			$infoForm = $this->_createInfoForm($bot);
			$queuesForm = $this->_createQueueForm($bot);
			$slicingForm = $this->_createSlicingForm($bot);
			$driverForm = $this->_createDriverForm($bot);

			// Add them to the wizard
			$wizard->add("Information / Details", $infoForm);
			$wizard->add("Queues", $queuesForm);
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
			} else if($queuesForm->checkSubmitAndValidate($this->args())) {
				$sql = "DELETE FROM bot_queues WHERE bot_id = ?";
				db()->execute($sql, array($bot->id));

				$priority = 1;
				$used = array();
				foreach($this->args() as $key => $value) {
					if(substr($key, 0, 6) === "queue-" && $value != 0) {
						if(in_array($value, $used))
							continue;
						else
							$used[] = $value;
						$sql = "INSERT INTO bot_queues VALUES(?, ?, ?)";
						$data = array(
							$value,
							$bot->id,
							$priority
						);
						$priority++;
						db()->execute($sql, $data);
					}
				}
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
					$bot->get('driver_name') == 's3g') {
					$config->port = $this->args('serial_port');
					$config->port_id = $this->args('port_id');
					$config->baud = $this->args('baudrate');
				}

				//did we get webcam info?
				if ($this->args('webcam_device')) {
					if(!isset($config->webcam))
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

			if($wizard->isFinished()) {
				$this->forwardToURL($bot->getUrl());
			}

			$this->set('bot_id', $bot->id);
			$this->set('wizard', $wizard);
		} catch (Exception $e) {
			$this->set('megaerror', $e->getMessage());
			$this->setTitle("Bot Edit - Error");
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
	 * @param $bot Bot
	 * @throws Exception
	 * @return Form
	 */
	private function _createQueueForm($bot)
	{

		$form = new Form('queue');
		if ($this->args('setup')) {
			$form->action = $bot->getUrl() . "/edit/setup";
		} else {
			$form->action = $bot->getUrl() . "/edit";
		}

		$form->add(
			DisplayField::name('title')
				->label('')
				->value('<h2>Queues</h2>')
		);

		$queueCount = 1;

		$allQueues = User::$me->getQueues()->getAll();
		$allQueueList = array(0 => '');
		foreach ($allQueues AS $row) {
			/* @var $q Queue */
			$q = $row['Queue'];
			$allQueueList[$q->id] = $q->getName();
		}

		if($form->isSubmitted($this->args())) {
			$used = array();
			foreach($this->args() as $key => $value) {
				if(substr($key, 0, 6) === "queue-") {
					if(!array_key_exists($value, $allQueueList))
						throw new Exception("That is not your queue!");
					if(in_array($value, $used))
						continue;
					else
						$used[] = $value;
					$form->add(
						SelectField::name($key)
							->id($key)
							->label('')
							->value($value)
							->options($allQueueList)
							->onchange('update_queues(this)')
					);
				}
			}
		} else {
			$queues = $bot->getQueues()->getAll();

			foreach ($queues as $row) {
				/** @var Queue $queue */
				$queue = $row['Queue'];
				$form->add(
					SelectField::name('queue-' . $queueCount)
						->id('queue-' . $queueCount)
						->label('')
						->value($queue->id)
						->options($allQueueList)
						->onchange('update_queues(this)')
				);
				$queueCount++;
			}

			$form->add(
				SelectField::name('queue-' . $queueCount)
					->id('queue-' . $queueCount)
					->label('')
					->value(0)
					->options($allQueueList)
					->onchange('update_queues(this)')
			);
		}

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

		$config = $bot->getDriverConfig();

		$form->add(
			CheckboxField::name('can_slice')
				->label('Client Slicing Enabled?')
				->help('Is the controlling computer fast enough to slice?')
				->value($config->can_slice)
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

		switch($bot->getStatus()) {
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
}