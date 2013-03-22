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

			$collection = User::$me->getBots();
      $per_page = 20;
      $page = $collection->putWithinBounds($this->args('page'), $per_page);
    
      $this->set('per_page', $per_page);
      $this->set('total', $collection->count());
      $this->set('page', $page);
      $this->set('bots', $collection->getPage($page, $per_page));
		}

		public function register()
		{
			$this->assertLoggedIn();
			
			$this->setTitle('Register a new Bot');
			
			$bot = new Bot();
			
			//load up our form.
			$form = $this->_createBotForm($bot);
			$form->action = "/bot/register";

			//handle our form
			if ($form->checkSubmitAndValidate($this->args()))
			{
			  $bot->set('user_id', User::$me->id);
				$bot->set('queue_id', $form->data('queue_id'));
        $bot->set('slice_engine_id', $form->data('slice_engine_id'));
        $bot->set('slice_config_id', $form->data('slice_config_id'));
				$bot->set('name', $form->data('name'));
				$bot->set('manufacturer', $form->data('manufacturer'));
				$bot->set('model', $form->data('model'));
				$bot->set('electronics', $form->data('electronics'));
				$bot->set('firmware', $form->data('firmware'));
				$bot->set('extruder', $form->data('extruder'));
				$bot->set('status', 'offline');
				$bot->save();

				Activity::log("registered the bot " . $bot->getLink() . ".");
			
				$this->forwardToUrl($bot->getUrl());						
			}
			
			$this->set('form', $form);
		}
		
		public function view()
		{
			$this->assertLoggedIn();

			try
			{
				//how do we find them?
				if ($this->args('id'))
					$bot = new Bot($this->args('id'));

				//did we really get someone?
				if (!$bot->isHydrated())
					throw new Exception("Could not find that bot.");
				if (!$bot->isMine())
					throw new Exception("You cannot view that bot.");
				
				$this->setTitle("View Bot - " . $bot->getName());
				
				//errors?
				$this->set('bot', $bot);
				$this->set('queue', $bot->getQueue());
				$this->set('job', $bot->getCurrentJob());
				$this->set('engine', $bot->getSliceEngine());
				$this->set('config', $bot->getSliceConfig());

				$jobs = $bot->getJobs(null, 'user_sort', 'DESC');
				$this->set('jobs', $jobs->getRange(0, 50));
				$this->set('job_count', $jobs->count());
				$this->set('stats', $bot->getStats());
				$this->set('owner', $bot->getUser());
				$this->set('errors', $bot->getErrorLog()->getRange(0, 50));
			}
			catch (Exception $e)
			{
				$this->set('megaerror', $e->getMessage());
				$this->setTitle("View Bot - Error");
			}
		}

		//TODO: convert to AJAX
		public function set_status()
		{
			$this->assertLoggedIn();

			try
			{
				//how do we find them?
				if ($this->args('id'))
					$bot = new Bot($this->args('id'));

				//did we really get someone?
				if (!$bot->isHydrated())
					throw new Exception("Could not find that bot.");
				if (!$bot->isMine())
					throw new Exception("You cannot view that bot.");
				if ($bot->get('status') == 'working' && $this->args('status') == 'offline')
					throw new Exception("You cannot take a working bot offline through the web interface.  You must stop the job from the client first.");
				
				if ($this->args('status') == 'offline')
					Activity::log("took the bot " . $bot->getLink() . " offline.");
				else
					Activity::log("brought the bot " . $bot->getLink() . " online.");

				$bot->set('status', $this->args('status'));
				$bot->save();
				
				$this->forwardToUrl("/");
			}
			catch (Exception $e)
			{
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

			try
			{
				//how do we find them?
				if ($this->args('id'))
					$bot = new Bot($this->args('id'));

				//did we really get someone?
				if (!$bot->isHydrated())
					throw new Exception("Could not find that bot.");
				if (!$bot->isMine())
					throw new Exception("You cannot view that bot.");

				$this->setTitle('Edit Bot - ' . $bot->getName());

				//load up our form.
				$form = $this->_createBotForm($bot);
				$form->action = $bot->getUrl() . "/edit";

				//handle our form
				if ($form->checkSubmitAndValidate($this->args()))
				{
					$bot->set('queue_id', $form->data('queue_id'));
					$bot->set('slice_engine_id', $form->data('slice_engine_id'));
					$bot->set('slice_config_id', $form->data('slice_config_id'));
					$bot->set('name', $form->data('name'));
					$bot->set('manufacturer', $form->data('manufacturer'));
					$bot->set('model', $form->data('model'));
					$bot->set('electronics', $form->data('electronics'));
					$bot->set('firmware', $form->data('firmware'));
					$bot->set('extruder', $form->data('extruder'));
					$bot->save();

					Activity::log("edited the bot " . $bot->getLink() . ".");
				
					$this->forwardToUrl($bot->getUrl());						
				}
				
				$this->set('form', $form);
			}
			catch (Exception $e)
			{
				$this->set('megaerror', $e->getMessage());
				$this->setTitle("Bot Edit - Error");
			}			
		}
		
		public function dropjob()
		{
			$this->assertLoggedIn();

			try
			{
				//how do we find them?
				if ($this->args('id'))
					$bot = new Bot($this->args('id'));

				//did we really get someone?
				if (!$bot->isHydrated())
					throw new Exception("Could not find that bot.");
				if (!$bot->isMine())
					throw new Exception("You cannot view that bot.");
        if (!($bot->get('status') == 'slicing' || $bot->get('status') == 'working'))
          throw new Exception("Bot must be slicing or working to drop a job.");
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
				if ($form->checkSubmitAndValidate($this->args()))
				{
          $bot->dropJob($job);

          //do we want to delete the job?
          if ($form->data('delete_job'))
            $job->delete();
          
          //do we want to go offline?
          if ($form->data('take_offline'))
          {
            $bot->set('status', 'offline');
            $bot->save();
          }
          
          //was there a job error?
          if ($form->data('job_error'))
          {
            if ($form->data('failure_reason') == 'Other')
      			  $error_text = $form->data('failure_reason_other');
      			else
      			  $error_text = $form->data('failure_reason');

            //log that shit!
            $log = $job->logError($error_text);
          }
          
					Activity::log("dropped the job " . $job->getLink() . ".");
				
					$this->forwardToUrl($bot->getUrl());						
				}
				
				$this->set('form', $form);
			}
			catch (Exception $e)
			{
				$this->set('megaerror', $e->getMessage());
				$this->setTitle("Bot Drop Job - Error");
			}			
		}
		
		public function _createJobDropForm($bot, $job)
		{
			$form = new Form();

			$form->add(new DisplayField(array(
				'name' => 'bot',
				'label' => 'Bot Name',
				'value' => $bot->getLink()
			)));

  		$form->add(new DisplayField(array(
  			'name' => 'job',
  			'label' => 'Job Name',
  			'value' => $job->getLink()
  		)));

  		$form->add(new CheckBoxField(array(
  			'name' => 'take_offline',
  			'label' => 'Take Offline',
  			'help' => 'Should the bot be taken offline afterwards?',
  			'value' => false
  		)));
  		
			$form->add(new CheckBoxField(array(
				'name' => 'delete_job',
				'label' => 'Delete Job',
				'help' => 'Do you want to delete this job?',
				'value' => false
			)));
			
  		$form->add(new CheckBoxField(array(
  			'name' => 'job_error',
  			'label' => 'Job/Bot Error',
  			'help' => 'Were there errors with the job or bot?',
  			'value' => false
  		)));
					
			$form->add(new SelectField(array(
				'name' => 'failure_reason',
				'label' => 'Reason for failure',
				'help' => 'Please enter a reason for rejecting this print.',
				'required' => true,
				'options' => self::$failure_options
			)));
			
			$form->add(new TextField(array(
				'name' => 'failure_reason_other',
				'label' => 'Other Reason',
				'help' => 'If you selected "other" above, please enter the reason here.',
				'required' => false,
				'value' => ""
			)));
		
			return $form;
		}

		public function delete()
		{
			$this->assertLoggedIn();

			try
			{
				//how do we find them?
				if ($this->args('id'))
					$bot = new Bot($this->args('id'));

				//did we really get someone?
				if (!$bot->isHydrated())
					throw new Exception("Could not find that bot.");
				if ($bot->get('user_id') != User::$me->id)
					throw new Exception("You do not own this bot.");
				if ($bot->get('status') == 'working')
					throw new Exception("You cannot delete bots that are currently working.  First, use the client software to cancel the job and then delete the bot.");

				$this->set('bot', $bot);
				$this->setTitle('Delete Bot - ' . $bot->getName());

				if ($this->args('submit'))
				{
					Activity::log("deleted the bot <strong>" . $bot->getName() . "</strong>.");

					$bot->delete();
					
					$this->forwardToUrl("/bots");
				}				
			}
			catch (Exception $e)
			{
				$this->setTitle('Delete Bot - Error');
				$this->set('megaerror', $e->getMessage());
			}			
		}

		public function listjobs()
		{
			$this->assertLoggedIn();

			try
			{
				//how do we find them?
				if ($this->args('id'))
					$bot = new Bot($this->args('id'));

				//did we really get someone?
				if (!$bot->isHydrated())
					throw new Exception("Could not find that bot.");
				if (!$bot->isMine())
					throw new Exception("You cannot view that bot.");
				$this->set('bot', $bot);
				
				$this->setTitle("Bot Jobs - " . $bot->getName());
				
				$collection = $bot->getJobs();
	      $per_page = 20;
	      $page = $collection->putWithinBounds($this->args('page'), $per_page);
    
	      $this->set('per_page', $per_page);
	      $this->set('total', $collection->count());
	      $this->set('page', $page);
	      $this->set('jobs', $collection->getPage($page, $per_page));	
				$this->set('status', $status);
			}
			catch (Exception $e)
			{
				$this->set('megaerror', $e->getMessage());
				$this->setTitle("View Bot - Error");
			}
		}
				
		private function _createBotForm($bot)
		{
			//load up our queues.
			$queues = User::$me->getQueues()->getAll();
			foreach ($queues AS $row)
			{
				$q = $row['Queue'];
				$qs[$q->id] = $q->getName();
			}

			//load up our engines.
	    if (User::isAdmin())
	      $engines = SliceEngine::getAllEngines()->getAll();
	    else
	     $engines = SliceEngine::getPublicEngines()->getAll();
      $engs[0] = "None";
			foreach ($engines AS $row)
			{
				$e = $row['SliceEngine'];
				$engs[$e->id] = $e->getName();
			}

      //load up our configs
      $engine = $bot->getSliceEngine();
      //       if (User::isAdmin())
      //   $configs = $engine->getAllConfigs()->getAll();
      // else
	      $configs = $engine->getMyConfigs()->getAll();
      if (!empty($configs))
      {
  			foreach ($configs AS $row)
  			{
  				$c = $row['SliceConfig'];
  				$cfgs[$c->id] = $c->getName();
  			}
      }
      else
        $cfgs[0] = "None";
			
			$form = new Form();
			
			$form->add(new TextField(array(
				'name' => 'name',
				'label' => 'Bot Name',
				'help' => 'What should humans call your bot?',
				'required' => true,
				'value' => $bot->get('name')
			)));
			
			$form->add(new SelectField(array(
				'name' => 'queue_id',
				'label' => 'Queue',
				'help' => 'Which queue does this bot pull jobs from?',
				'required' => true,
				'value' => $bot->get('queue_id'),
				'options' => $qs
			)));

  		$form->add(new SelectField(array(
  		  'id' => 'slice_engine_dropdown',
  			'name' => 'slice_engine_id',
  			'label' => 'Slice Engine',
  			'help' => 'Which slicing engine does this bot use?',
  			'required' => false,
  			'value' => $bot->get('slice_engine_id'),
  			'options' => $engs,
  			'onchange' => 'update_slice_config_dropdown(this)'
  		)));

  		$form->add(new SelectField(array(
  		  'id' => 'slice_config_dropdown',
  			'name' => 'slice_config_id',
  			'label' => 'Slice Configuration',
  			'help' => 'Which slicing configuration to use? <a href="/slicers">click here</a> to view/edit configs.',
  			'required' => false,
  			'value' => $bot->get('slice_config_id'),
  			'options' => $cfgs
  		)));

			$form->add(new TextField(array(
				'name' => 'manufacturer',
				'label' => 'Manufacturer',
				'help' => 'Which company (or person) built your bot?',
				'required' => true,
				'value' => $bot->get('manufacturer')
			)));

			$form->add(new TextField(array(
				'name' => 'model',
				'label' => 'Model',
				'help' => 'What is the model or name of your bot design?',
				'required' => true,
				'value' => $bot->get('model')
			)));

			$form->add(new TextField(array(
				'name' => 'electronics',
				'label' => 'Electronics',
				'help' => 'What electronics are you using to control your bot?',
				'required' => true,
				'value' => $bot->get('electronics')
			)));

			$form->add(new TextField(array(
				'name' => 'firmware',
				'label' => 'Firmware',
				'help' => 'What firmware are you running on your electronics?',
				'required' => true,
				'value' => $bot->get('firmware')
			)));

  		$form->add(new TextField(array(
  			'name' => 'extruder',
  			'label' => 'Extruder',
  			'help' => 'What extruder are you using to print with?',
  			'required' => true,
  			'value' => $bot->get('extruder')
  		)));
					
			return $form;
		}
		
		public function slice_config_select()
		{
      //load up our configs
      $engine = new SliceEngine($this->args('id'));
      //       if (User::isAdmin())
      //   $configs = $engine->getAllConfigs()->getAll();
      // else
	      $configs = $engine->getMyConfigs()->getAll();
      if (!empty($configs))
      {
  			foreach ($configs AS $row)
  			{
  				$c = $row['SliceConfig'];
  				echo '<option value="' . $c->id . '">' . $c->getName() . '</option>' . "\n";
  			}
      }
      else
				echo '<option value="0">None</option>' . "\n";

      exit;
		}

    public function statusbutton()
    {
      $bot = $this->args('bot');
			$this->set('bot', $bot);
    }
	}
?>
