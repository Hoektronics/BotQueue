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

class SlicerController extends Controller
{

	public function home()
	{
		$this->setTitle("Slicer Engines");
		$this->set('area', 'slicers');

		if (User::isAdmin())
			$this->set('slicers', SliceEngine::getAllEngines()->getAll());
		else
			$this->set('slicers', SliceEngine::getPublicEngines()->getAll());

		if (User::isLoggedIn())
			$this->set('configs', User::$me->getMySliceConfigs()->getAll());
	}

	public function create()
	{
		$this->assertLoggedIn();

		$this->setTitle('Create Slice Engine');
		$this->set('area', 'slicers');

		try {
			if (!User::isAdmin())
				throw new Exception("You must be an admin to create slice engines.");

			//setup some objects
			$engine = new SliceEngine();
			$form = $this->_createSliceEngineForm($engine);
			$form->action = "/slicer/create";
			$this->set('form', $form);

			//check our form
			if ($form->checkSubmitAndValidate($this->args())) {
				//first create our engine object
				$engine->set('engine_name', $form->data('engine_name'));
				$engine->set('engine_path', $form->data('engine_path'));
				$engine->set('is_featured', $form->data('is_featured'));
				$engine->set('is_public', $form->data('is_public'));
				$engine->set('add_date', date("Y-m-d H:i:s"));
				$engine->save();

				//now we make it a default config object
				$config = new SliceConfig();
				$config->set('config_name', 'Default');
				$file = $form->data('config_file');
				$config->set('config_data', file_get_contents($file['tmp_name']));
				$config->set('engine_id', $engine->id);
				$config->set('user_id', User::$me->id);
				$config->set('add_date', date("Y-m-d H:i:s"));
				$config->set('edit_date', date("Y-m-d H:i:s"));
				$config->save();

				//now record our default id
				$engine->set('default_config_id', $config->id);
				$engine->save();

				//send us to view the new engine.
				$this->forwardToUrl($engine->getUrl());
			}
		} catch (Exception $e) {
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function import()
	{
		// todo Until BotQueue is linked up with github, we have no real way of
		// deciding if someone should be able to add engines or not since this
		// currently adds everything publicly. Most likely, if the one who pushed it
		// is linked to an admin user, then it will go through and be public. If it's
		// linked to a normal user, then it won't be public.
		// tl;dr If you uncomment the lines bellow, then any repo that has a webhook
		// pointed here can add slicers.
//		$github_info = array();
//		if($this->args('payload'))
//			$github_info = json_decode($this->args('payload'),true);

//		if(isset($github_info['repository']) && isset($github_info['repository']['url'])) {
//			$github_url = $github_info['repository']['url'];
//			$github_url = str_replace("http://", "", $github_url);
//			$github_url = str_replace("https://", "", $github_url);
//			$github_url = str_replace("github.com/", "", $github_url);
//		} else {
		$github_url = "Hoektronics/engines";
//		}

		try {
			$this->setTitle('Import Slice Engine');
			$this->set('area', 'slicers');

			$response = Utility::downloadUrl("https://api.github.com/repos/{$github_url}/git/refs");

			if ($response === False)
				throw new Exception("I'm sorry, I couldn't access github");
			$json = json_decode(file_get_contents($response['localpath']), True);

			if (isset($json['message']) && $json['message'] == "Not Found") {
				throw new Exception("I'm sorry, but the repo specified doesn't exist");
			}

			$engines = array();
			foreach ($json as $ref) {
				if (isset($ref['object']) && isset($ref['object']['type']) && $ref['object']['type'] == "tag") {

					$tag = substr(strrchr($ref['ref'], '/'), 1);

					$split = explode("-", $tag);
					$os = $split[0];
					$engineName = $split[1];
					$version = $split[2];
					$engine_path = $engineName . '-' . $version;

					if (!SliceEngine::engine_exists($engine_path)) { // Do we already have this engine?
						$manifestResponse = Utility::downloadUrl("https://raw.github.com/{$github_url}/" . $tag . "/manifest.json");
						$manifest = json_decode(file_get_contents($manifestResponse['localpath']), True);

						$engine = new SliceEngine();
						$engine->set('engine_name', Utility::capitalize($engineName) . ' ' . $version);
						$engine->set('engine_path', $engine_path);
						$engine->set('is_featured', false);
						$engine->set('is_public', true);
						$engine->set('add_date', date("Y-m-d H:i:s"));
						$engine->save();

						//now we make it a default config object
						$config = new SliceConfig();
						$config->set('config_name', 'Default');
						$file = Utility::downloadUrl("https://raw.github.com/{$github_url}/" . $tag . "/" . $manifest['configuration']);
						$config->set('config_data', file_get_contents($file['localpath']));
						$config->set('engine_id', $engine->id);
						$config->set('user_id', null); // Default config is for everyone
						$config->set('add_date', date("Y-m-d H:i:s"));
						$config->set('edit_date', date("Y-m-d H:i:s"));
						$config->save();

						//now record our default id
						$engine->set('default_config_id', $config->id);
						$engine->save();

						// Store them in case we want to display them.
						$engines[] = $engine;
					}

					// We may have the engine, but now we need to make sure we know about every version
					SliceEngine::validOS($engine_path, $os);
				}
			}
			if ($this->args('payload')) {
				print("Payload: " . $this->args('payload') . "\n");
				die("Update from Github worked");
			}
			$this->forwardToURL("/slicers");
		} catch (Exception $e) {
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function edit()
	{
		$this->assertLoggedIn();
		$this->set('area', 'slicers');

		try {
			if (!User::isAdmin())
				throw new Exception("You must be an admin to create slice engines.");

			//load the data and check for errors.
			$engine = new SliceEngine($this->args('id'));
			if (!$engine->isHydrated())
				throw new Exception("That slice engine does not exist.");

			//setup some objects
			$form = $this->_createSliceEngineForm($engine);
			$form->action = $engine->getUrl() . "/edit";
			$this->set('form', $form);

			$this->setTitle("Edit Slice Engine - " . $engine->getName());

			//check our form
			if ($form->checkSubmitAndValidate($this->args())) {
				//first create our engine object
				$engine->set('engine_name', $form->data('engine_name'));
				$engine->set('engine_path', $form->data('engine_path'));
				$engine->set('is_featured', $form->data('is_featured'));
				$engine->set('is_public', $form->data('is_public'));
				$engine->save();

				//now we make it a default config object
				$file = $form->data('config_file');
				if (!empty($file)) {
					$config = $engine->getDefaultConfig();
					$config->set('config_data', file_get_contents($file['tmp_name']));
					$config->set('edit_date', date("Y-m-d H:i:s"));
					$config->save();
				}

				//send us to view the engine.
				$this->forwardToUrl($engine->getUrl());
			}
		} catch (Exception $e) {
			$this->setTitle("Edit Slice Engine - Error");
			$this->set('megaerror', $e->getMessage());
		}
	}

	/**
	 * @param $engine SliceEngine
	 * @return Form
	 */
	public function _createSliceEngineForm($engine)
	{
		$form = new Form();
		//$config = $engine->getDefaultConfig();

		$form->add(
			TextField::name('engine_name')
				->label('Engine Name')
				->help('What is the name of this slicing engine.  Include the version number.  Eg: MySlice v3.2.1')
				->required(true)
				->value($engine->get('engine_name'))
		);

		$form->add(
			TextField::name('engine_path')
				->label('Engine Path')
				->help('What is the path to the slicing engine from the bumblebee/slicers directory?  Eg: myslice-3.2.1')
				->required(true)
				->value($engine->get('engine_path'))
		);

		$form->add(
			CheckboxField::name('is_public')
				->label('Is this slice engine public and available for use?')
				->help('Check this box when you are ready to roll out a new slice engine.')
				->value($engine->get('is_public'))
		);

		$form->add(
			CheckboxField::name('is_featured')
				->label('Is this slice engine featured?')
				->help('Featured slice engines will be more prominently featured, and will make it easier to use the latest and greatest slicing tech.')
				->value($engine->get('is_featured'))
		);

		$form->add(
			UploadField::name('config_file')
				->label('Default Configuration')
				->help('Upload the default configuration text for this engine (.ini for Slic3r)')
				->required(!(bool)$engine->id)
		);

		return $form;
	}

	public function view()
	{
		$this->set('area', 'slicers');

		try {
			//load the data and check for errors.
			$engine = new SliceEngine($this->args('id'));
			if (!$engine->isHydrated())
				throw new Exception("That slice engine does not exist.");
			if (!$engine->get('is_public') && !User::isAdmin())
				throw new Exception("You do not have access to view this slice engine.");

			//save our engine
			$this->set('engine', $engine);

			$this->setTitle("Slice Engine - " . $engine->getLink());

			//pull in our configs
			if (User::isAdmin())
				$this->set('configs', $engine->getAllConfigs()->getAll());
			else
				$this->set('configs', $engine->getMyConfigs()->getAll());
		} catch (Exception $e) {
			$this->setTitle("Slice Engine - Error");
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function delete()
	{
		$this->assertLoggedIn();
		$this->set('area', 'slicers');

		try {
			//load the data and check for errors.
			$engine = new SliceEngine($this->args('id'));
			if (!$engine->isHydrated())
				throw new Exception("That slice engine does not exist.");
			if (!User::isAdmin())
				throw new Exception("You do not have access to delete this slice engine.");

			$this->setTitle("Delete Slice Engine - " . $engine->getName());

			//create our form
			$form = new Form();
			$form->action = $engine->getUrl() . "/delete";
			$form->add(
				WarningField::name('warning')
					->value("<strong>Warning</strong>: deleting the " .
						$engine->getLink() .
						" slice engine will delete all slice configs associated with it, and likely break a ton of stuff.  Are you really sure you want to do this?")
			);

			$this->set('form', $form);

			//check our form
			if ($form->checkSubmitAndValidate($this->args())) {
				$engine->delete();
				$this->forwardToUrl("/slicers");
			}
		} catch (Exception $e) {
			$this->setTitle("Delete Slice Engine - Error");
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function config_create()
	{
		$this->assertLoggedIn();
		$this->set('area', 'slicers');

		try {
			//load the data and check for errors.
			if ($this->args('id')) {
				$engine = new SliceEngine($this->args('id'));

				if (!$engine->isHydrated())
					throw new Exception("That slice engine does not exist.");
				if (!$engine->get('is_public') || !User::isAdmin())
					throw new Exception("You do not have access to view this slice engine.");
				$this->setTitle("Create Slice Config - " . $engine->getName());

			} else {
				$this->setTitle("Create Slice Config");
				$engine = new SliceEngine();
			}

			//setup some objects
			$config = new SliceConfig();

			// If the engine has an id, set the engine_id in the config
			if ($engine->id) {
				$config->set('engine_id', $engine->id);
			}

			$form = $this->_createSliceConfigUploadForm($config);

			if ($engine->id) {
				$form->action = $engine->getUrl() . "/createconfig";
			} else {
				$form->action = "/slicer/createconfig";
			}

			$this->set('form', $form);

			//check our form
			if ($form->checkSubmitAndValidate($this->args())) {
				//now we make it a config object
				$config->set('config_name', $form->data('config_name'));
				$file = $form->data('config_file');
				$config->set('config_data', file_get_contents($file['tmp_name']));
				$config->set('engine_id', $form->data('engine_id'));
				$config->set('user_id', User::$me->id);
				$config->set('add_date', date("Y-m-d H:i:s"));
				$config->set('edit_date', date("Y-m-d H:i:s"));
				$config->save();

				//send us to view the new engine.
				$this->forwardToUrl($config->getUrl());
			}
		} catch (Exception $e) {
			$this->setTitle("Create Slice Config - Error");
			$this->set('megaerror', $e->getMessage());
		}
	}

	/**
	 * @param $config SliceConfig
	 * @return Form
	 */
	public function _createSliceConfigRawForm($config)
	{
		$form = new Form('raw');

		//load up our engines.
		if (User::isAdmin())
			$availableEngines = SliceEngine::getAllEngines()->getAll();
		else
			$availableEngines = SliceEngine::getPublicEngines()->getAll();
		$engines = array();
		foreach ($availableEngines AS $row) {
			/* @var $e SliceEngine */
			$e = $row['SliceEngine'];
			$engines[$e->id] = $e->getName();
		}

		$form->add(
			SelectField::name('engine_id')
				->label('Slice Engine')
				->help('Which slicing engine does this config use?')
				->required(true)
				->value($config->get('engine_id'))
				->options($engines)
		);

		$form->add(
			TextField::name('config_name')
				->label('Config Name')
				->help('What is the name of this slicing configuration.')
				->required(true)
				->value($config->get('config_name'))
		);

		if ($config->isHydrated())
			$form->add(
				CheckboxField::name('expire_slicejobs')
					->label('Expire Old Slice Jobs')
					->help('If checked, old slice jobs will be expired and never re-used.')
					->value(1)
			);

		$form->add(
			TextareaField::name('default_config')
				->label('Raw Configuration Text')
				->help('Edit the raw configuration text for this engine.')
				->required(true)
				->width('60%')
				->rows('20')
				->value($config->get('config_data'))
		);

		return $form;
	}

	/**
	 * @param $config SliceConfig
	 * @return Form
	 */
	public function _createSliceConfigUploadForm($config)
	{
		$form = new Form('upload');

		//load up our engines.
		if (User::isAdmin())
			$engines = SliceEngine::getAllEngines()->getAll();
		else
			$engines = SliceEngine::getPublicEngines()->getAll();
		$engs = array();
		foreach ($engines AS $row) {
			/* @var $e SliceEngine */
			$e = $row['SliceEngine'];
			$engs[$e->id] = $e->getName();
		}
		$form->add(
			SelectField::name('engine_id')
				->label('Slice Engine')
				->help('Which slicing engine does this config use?')
				->required(true)
				->value($config->get('engine_id'))
				->options($engs)
		);
		$form->add(
			TextField::name('config_name')
				->label('Config Name')
				->help('What is the name of this slicing configuration.')
				->required(true)
				->value($config->get('config_name'))
		);

		if ($config->isHydrated())
			$form->add(
				CheckboxField::name('expire_slicejobs')
					->label('Expire Old Slice Jobs')
					->help('If checked, old slice jobs will be expired and never re-used.')
					->value(1)
			);

		$form->add(
			UploadField::name('config_file')
				->label('Configuration File')
				->help('The configuration file to use (.ini for Slic3r)')
				->required(true)
		);

		return $form;
	}

	public function config_edit()
	{
		$this->assertLoggedIn();
		$this->set('area', 'slicers');

		try {
			$id = $this->args('id');
			/** @var SliceConfig $config */
			$config = new SliceConfig($id);
			if (!$config->isHydrated())
				throw new Exception("That slice config does not exist.");

			// Make a copy if it's the default
			$default_config = $config->getEngine()->getDefaultConfig();
			if($config->id == $default_config->id) {
				$config = new SliceConfig();
				$config->set('config_data', $default_config->get('config_data'));
				$config->set('engine_id', $default_config->get('engine_id'));
				$config->set('add_date', date("Y-m-d H:i:s"));
				$config->set('user_id', User::$me->id);
				$config->set('fork_id', $default_config->id);
			}

			if (User::$me->id != $config->get('user_id') && !User::isAdmin())
				throw new Exception("You cannot edit this slice config.");

			$this->setTitle("Edit Slice Config - " . $config->getName());

			//setup some objects
			$raw_form = $this->_createSliceConfigRawForm($config);
			if($id == $default_config->id)
				$raw_form->action = $default_config->getUrl() . "/edit";
			else
				$raw_form->action = $config->getUrl() . "/edit";
			$this->set('rawform', $raw_form);

			//setup some objects
			$upload_form = $this->_createSliceConfigUploadForm($config);
			if($id == $default_config->id)
				$upload_form->action = $default_config->getUrl() . "/edit";
			else
				$upload_form->action = $config->getUrl() . "/edit";
			$this->set('uploadform', $upload_form);

			//check our form
			if ($raw_form->checkSubmitAndValidate($this->args())) {
				//edit the config object
				$config->set('engine_id', $raw_form->data('engine_id'));
				$config->set('config_name', $raw_form->data('config_name'));
				$config->set('config_data', $raw_form->data('default_config'));
				$config->set('edit_date', date("Y-m-d H:i:s"));
				$config->save();

				//are we expiring the old slice jobs?
				if ($raw_form->data('expire_slicejobs'))
					$config->expireSliceJobs();

				//send us to view the engine.
				$this->forwardToUrl($config->getUrl());
			} else if ($upload_form->checkSubmitAndValidate($this->args())) {
				//edit the config object
				$config->set('config_name', $upload_form->data('config_name'));
				$config->set('engine_id', $upload_form->data('engine_id'));

				$file = $upload_form->data('config_file');
				$config->set('config_data', file_get_contents($file['tmp_name']));
				$config->set('edit_date', date("Y-m-d H:i:s"));
				$config->save();

				//are we expiring the old slice jobs?
				if ($upload_form->data('expire_slicejobs'))
					$config->expireSliceJobs();

				//send us to view the engine.
				$this->forwardToUrl($config->getUrl());
			}
		} catch (Exception $e) {
			$this->setTitle("Edit Slice Config - Error");
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function config_view()
	{
		$this->assertLoggedIn();
		$this->set('area', 'slicers');

		try {
			//load the data and check for errors.
			$config = new SliceConfig($this->args('id'));
			if (!$config->isHydrated())
				throw new Exception("That slice config does not exist.");
			if ($config->get('user_id') != User::$me->id && !User::isAdmin() && $config->get('user_id') != null)
				throw new Exception("You do not have access to view this config.");

			//pull in all our data.
			$this->set('config', $config);
			$this->set('engine', $config->getEngine());
			$this->set('user', $config->getUser());
			$this->set('jobs', $config->getSliceJobs()->getAll());
			$this->set('bots', $config->getBots()->getAll());

			$this->setTitle("Slice Config - " . $config->getLink());

		} catch (Exception $e) {
			$this->setTitle("Slice Config - Error");
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function config_delete()
	{
		$this->assertLoggedIn();
		$this->set('area', 'slicers');

		try {
			//load the data and check for errors.
			$config = new SliceConfig($this->args('id'));
			if (!$config->isHydrated())
				throw new Exception("That slice config does not exist.");
			if ($config->get('user_id') != User::$me->id && !User::isAdmin())
				throw new Exception("You do not have access to delete this config.");

			$this->setTitle("Delete Slice Config - " . $config->getName());

			//create our form
			$form = new Form();
			$form->action = $config->getUrl() . "/delete";
			$form->add(
				WarningField::name('warning')
					->value("<strong>Warning</strong>: deleting the " . $config->getLink() . " slice config is permanent.  Are you really sure you want to do this?")
			);

			$this->set('form', $form);

			//check our form
			if ($form->checkSubmitAndValidate($this->args())) {
				$config->delete();
				$this->forwardToUrl("/slicers");
			}
		} catch (Exception $e) {
			$this->setTitle("Delete Slice Config - Error");
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function draw_jobs()
	{
		$this->setArg('jobs');
	}

	public function draw_jobs_small()
	{
		$this->setArg('jobs');
	}

	public function job_view()
	{
		$this->set('area', 'jobs');

		try {
			//load the data and check for errors.
			$job = new SliceJob($this->args('id'));
			if (!$job->isHydrated())
				throw new Exception("That slice job does not exist.");
			if ($job->get('user_id') != User::$me->id)
				throw new Exception("You do not have access to view this slice job.");

			//save our engine
			$this->set('job', $job);
			$this->set('inputfile', $job->getInputFile());
			$this->set('outputfile', $job->getOutputFile());
			$this->set('config', $job->getSliceConfig());
			/* @var $config SliceConfig */
			$config = $this->get('config');
			$this->set('engine', $config->getEngine());

			$this->setTitle("Slice Job - " . $job->getLink());
		} catch (Exception $e) {
			$this->setTitle("Slice Job - Error");
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function job_update()
	{
		$this->set('area', 'jobs');

		try {
			//load the data and check for errors.
			$job = new SliceJob($this->args('id'));
			if (!$job->isHydrated())
				throw new Exception("That slice job does not exist.");
			if ($job->get('user_id') != User::$me->id)
				throw new Exception("You do not have access to view this slice job.");
			if ($job->get('status') != 'pending')
				throw new Exception("This slice job is not in a pending state.");

			if ($this->args('pass'))
				$job->pass();
			if ($this->args('fail'))
				$job->fail();

			$this->forwardToUrl($job->getUrl());
		} catch (Exception $e) {
			$this->setTitle("Slice Job - Error");
			$this->set('megaerror', $e->getMessage());
		}
	}
}
