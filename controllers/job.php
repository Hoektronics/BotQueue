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

	class JobController extends Controller
	{
		public function home()
		{
			$this->assertLoggedIn();
			
			$this->setTitle(User::$me->getName() . "'s Jobs");
			
			$available = User::$me->getJobs('available');
			$this->set('available', $available->getRange(0, 10));
			$this->set('available_count', $available->count());
			
			$taken = User::$me->getJobs('taken');
			$this->set('taken', $taken->getRange(0, 10));
			$this->set('taken_count', $taken->count());
			
			$complete = User::$me->getJobs('complete');
			$this->set('complete', $complete->getRange(0, 10));
			$this->set('complete_count', $complete->count());
			
			$failure = User::$me->getJobs('failure');
			$this->set('failure', $failure->getRange(0, 10));
			$this->set('failure_count', $failure->count());
		}
		
		public function listjobs()
		{
			$this->assertLoggedIn();
			
			$status = $this->args('status');

			try
			{
				if ($status == 'available')
					$this->setTitle(User::$me->getName() . "'s Available Jobs");
				else if ($status == 'taken')
					$this->setTitle(User::$me->getName() . "'s Working Jobs");
				else if ($status == 'complete')
					$this->setTitle(User::$me->getName() . "'s Finished Jobs");
				else if ($status == 'failure')
					$this->setTitle(User::$me->getName() . "'s Failed Jobs");
				else
					throw new Exception("That is not a valid status!");
				
				$collection = User::$me->getJobs($status);
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
				$this->setTitle('View Jobs - Error');
				$this->set('megaerror', $e->getMessage());
			}
		}

		public function view()
		{
			$this->assertLoggedIn();

			try
			{
				//how do we find them?
				if ($this->args('id'))
					$job = new Job($this->args('id'));

				//did we really get someone?
				if (!$job->isHydrated())
					throw new Exception("Could not find that job.");

				$this->setTitle('View Job - ' . $job->getName());
				
				//errors?
				if (!$this->get('megaerror'))
				{
					$this->setTitle('View Job - ' . $job->getName());

					$this->set('job', $job);
					$this->set('gcode_file', $job->getFile());
					$this->set('source_file', $job->getSourceFile());
					$this->set('slicejob', $job->getSliceJob());
					$this->set('sliceengine', $this->get('slicejob')->getSliceEngine());
					$this->set('sliceconfig', $this->get('slicejob')->getSliceConfig());
					$this->set('queue', $job->getQueue());
					$this->set('bot', $job->getBot());
					$this->set('creator', $job->getUser());
					$this->set('errors', $job->getErrorLog()->getAll());
				}
			}
			catch (Exception $e)
			{
				$this->setTitle('View Job - Error');
				$this->set('megaerror', $e->getMessage());
			}
		}

		public function edit()
		{
			$this->assertLoggedIn();

			try
			{
				//how do we find them?
				if ($this->args('id'))
					$job = new Job($this->args('id'));

				//did we really get someone?
				if (!$job->isHydrated())
					throw new Exception("Could not find that job.");
				if ($job->get('user_id') != User::$me->id)
					throw new Exception("You do not own this job.");
				if ($job->get('status') != 'available')
					throw new Exception("You can only edit jobs that have not been taken yet.");

				$this->setTitle('Edit Job - ' . $job->getName());
				$this->set('job', $job);

				//load up our queues.
				$queues = User::$me->getQueues()->getAll();
				foreach ($queues AS $row)
				{
					$q = $row['Queue'];
					$data[$q->id] = $q->getName();
				}
				$this->set('queues', $data);
				
				if ($this->args('submit'))
				{
					$queue = new Queue($this->args('queue_id'));
					if (!$queue->canAdd())
						throw new Exception("That is not a valid queue.");
					
					$job->set('queue_id', $queue->id);
					$job->save();

					Activity::log("edited the job " . $job->getLink() . ".");
					
					$this->forwardToUrl($job->getUrl());
				}
				
				//errors?
				if (!$this->get('megaerror'))
				{
					$this->set('file', $job->getFile());
					$this->set('queue', $job->getQueue());
					$this->set('bot', $job->getBot());
					$this->set('creator', $job->getUser());
				}
			}
			catch (Exception $e)
			{
				$this->setTitle('Edit Job - Error');
				$this->set('megaerror', $e->getMessage());
			}
		}

		public function delete()
		{
			$this->assertLoggedIn();

			try
			{
				//how do we find them?
				if ($this->args('id'))
					$job = new Job($this->args('id'));

				//did we really get someone?
				if (!$job->isHydrated())
					throw new Exception("Could not find that job.");
				if ($job->get('user_id') != User::$me->id)
					throw new Exception("You do not own this job.");
				if ($job->get('status') == 'taken')
					throw new Exception("You cannot delete jobs that are in progress from the web.  Cancel it from the client software instead.");

				$this->set('job', $job);
				$this->setTitle('Delete Job - ' . $job->getName());

				if ($this->args('submit'))
				{
					Activity::log("deleted the job <strong>" . $job->getName() . "</strong>.");

					$job->delete();
					
					$this->forwardToUrl("/jobs");
				}				
			}
			catch (Exception $e)
			{
				$this->setTitle('Delete Job - Error');
				$this->set('megaerror', $e->getMessage());
			}			
		}

		public function qa()
		{
			$this->assertLoggedIn();

			try
			{
				//how do we find them?
				if ($this->args('id'))
					$job = new Job($this->args('id'));

				//did we really get someone?
				if (!$job->isHydrated())
					throw new Exception("Could not find that job.");
				if ($job->get('user_id') != User::$me->id)
					throw new Exception("You do not own this job.");
				if ($job->get('status') != 'qa')
					throw new Exception("You cannot do QA on this job.");

		    $bot = $job->getBot();

				$this->set('job', $job);
				$this->set('bot', $bot);
				$this->set('gcode_file', $job->getFile());
				$this->set('source_file', $job->getSourceFile());
				
				$this->setTitle('Verify Job - ' . $job->getName());	
				
				$form = $this->_createQAFailForm($job);
				$this->set('form', $form);
			}
			catch (Exception $e)
			{
				$this->setTitle('Delete Job - Error');
				$this->set('megaerror', $e->getMessage());
			}			
		}

		public function qa_pass()
		{
			$this->assertLoggedIn();

			try
			{
				//how do we find them?
				if ($this->args('id'))
					$job = new Job($this->args('id'));

				//did we really get someone?
				if (!$job->isHydrated())
					throw new Exception("Could not find that job.");
				if ($job->get('user_id') != User::$me->id)
					throw new Exception("You do not own this job.");
				if ($job->get('status') != 'qa')
					throw new Exception("You cannot do QA on this job.");

		    $bot = $job->getBot();

				$this->set('job', $job);
				$this->set('bot', $bot);
				
				if ($this->args('submit'))
				{
			    $bot->set('job_id', 0);
    			$bot->set('status', 'idle');
    			$bot->save();
    			
    			$job->set('status', 'complete');
    			$job->set('verified_time', date("Y-m-d H:i:s"));
    			$job->save();
    			
					Activity::log("accepted the output of job " . $job->getLink() . ".");

          $this->forwardToUrl("/");  
			  }
        else
        {
          $this->forwardToUrl($job->getUrl() . "/qa");  
        }				
			}
			catch (Exception $e)
			{
				$this->setTitle('Accept Job - Error');
				$this->set('megaerror', $e->getMessage());
			}			
		}

		public function qa_fail()
		{
			$this->assertLoggedIn();

			try
			{
				//how do we find them?
				if ($this->args('id'))
					$job = new Job($this->args('id'));

				//did we really get someone?
				if (!$job->isHydrated())
					throw new Exception("Could not find that job.");
				if ($job->get('user_id') != User::$me->id)
					throw new Exception("You do not own this job.");
				if ($job->get('status') != 'qa')
					throw new Exception("You cannot do QA on this job.");

		    $bot = $job->getBot();

				$this->set('job', $job);
				$this->set('bot', $bot);
				
				$form = $this->_createQAFailForm($job);
				$this->set('form', $form);
				
				if ($form->checkSubmitAndValidate($this->args()))
				{
				  if ($form->data('failure_reason') == 'Other')
    			  $error_text = $form->data('failure_reason_other');
    			else
    			  $error_text = $form->data('failure_reason');

          //log that shit!
          $log = $job->logError($error_text);
    			  
			    if ($form->data('bot_error'))
			    {
				    $bot->set('job_id', 0);
      			$bot->set('status', 'error');
    			  $bot->set('error_text', $error_text);
      			$bot->save();
      			
      			Activity::log("took the bot " . $bot->getLink() . "offline for repairs.");
			    }
			    else
			    {
				    $bot->set('job_id', 0);
      			$bot->set('status', 'idle');
      			$bot->save();
			    }
			    
			    if ($form->data('job_error'))
			    {
			      $job->set('status', 'failure');
			      $job->set('verified_time', date("Y-m-d H:i:s"));
			      $job->save();
			      
			      Activity::log("dropped the job " . $job->getLink() . ".");
			    }
			    else
			    {
			      $job->set('status', 'available');
			      $job->set('taken_time', 0);
			      $job->set('downloaded_time', 0);
			      $job->set('finished_time', 0);
			      $job->set('verified_time', 0);
			      $job->set('bot_id', 0);
			      $job->save();
			    }

					Activity::log("rejected the output of job " . $job->getLink() . ".");

          $this->forwardToUrl("/");  
				}
				else
			  	$this->forwardToUrl($job->getUrl() . "/qa"); 				
			}
			catch (Exception $e)
			{
				$this->setTitle('Reject Job - Error');
				$this->set('megaerror', $e->getMessage());
			}			
		}
		
		public function _createQAFailForm($job)
		{
		  $form = new Form();
		  $form->action = $job->getUrl() . "/qa/fail";
		  
		  $options = array(
		    "Unknown" => "Unknown Failure",
		    "Extruder Jam" => "Extruder Jam (Stopped extrusion, filament stripped, etc.)",
		    "XY Offset" => "XY Layers Offset (Motors skipping, etc.)",
		    "Print Dislodged" => "Print dislodged from build platform",
		    "Machine Frozen" => "Machine frozen and not responding (software crash, etc.)",
		    "Out of Filament" => "Ran out of filament, print did not complete.",
		    "Poor Quality" => "Poor print quality (blobbing, loose threads, etc.)",
		    "Other" => "Other - Please enter reason in field below."
		  );
					
			$form->add(new SelectField(array(
				'name' => 'failure_reason',
				'label' => 'Reason for failure',
				'help' => 'Please enter a reason for rejecting this print.',
				'required' => true,
				'options' => $options
			)));
			
			$form->add(new TextField(array(
				'name' => 'failure_reason_other',
				'label' => 'Other Reason',
				'help' => 'If you selected "other" above, please enter the reason here.',
				'required' => false,
				'value' => ""
			)));
			
			$form->add(new CheckboxField(array(
			 'name' => 'bot_error',
			 'label' => 'Put the bot in error/maintenance mode?',
			 'help' => 'Check this box if the bot needs maintenance and should stop grabbing jobs.',
			 'value' => 1
			)));
			
			$form->add(new CheckboxField(array(
			 'name' => 'job_error',
			 'label' => 'Pull this job from the queue?',
			 'help' => 'Check this box if the job itself has issues and should be pulled from the queue.'
			)));
			
			return $form;
		}
		
		public function file()
		{
			try
			{
				//how do we find them?
				if ($this->args('id'))
					$file = new S3File($this->args('id'));

				//did we really get someone?
				if (!$file->isHydrated())
					throw new Exception("Could not find that file.");
				if ($file->get('user_id') != User::$me->id)
					throw new Exception("You do not own that file.");
			
				$this->setTitle($file->getName());

				$this->set('file', $file);
				$this->set('creator', $file->getUser());
				
				$jobs = $file->getJobs();
				$this->set('jobs', $jobs->getRange(0, 10));
				$this->set('job_count', $jobs->count());
			}
			catch (Exception $e)
			{
				$this->setTitle('View File - Error');
				$this->set('megaerror', $e->getMessage());
			}
		}

		
		public function passthru()
		{
		  $this->assertLoggedIn();
		  
		  try
		  {
  		  $file = new S3File($this->args('id'));
  		  if (!$file->isHydrated())
  		    throw new Exception("This file does not exist.");
  		    
  		  if ($file->get('user_id') != User::$me->id)
  		    throw new Exception("This is not your file.");
  		    
  		  //get our headers ready.
        header('Content-Description: File Transfer');
        if ($file->get('type'))
          header('Content-Type: ' . $file->get('type'));
        else
          header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($file->getRealUrl()));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . (int)$file->get('size'));

        //kay, send it
        readfile($file->getRealUrl());
        exit;
		  }
		  catch (Exception $e)
		  {
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

		public function create()
		{
			$this->assertLoggedIn();
			
			if ($this->args('step2'))
				$this->setTitle('Step 2 of 2: Create Job');
			else
				$this->setTitle('Create new Job');
				
			try
			{
				if ($this->args('job_id'))
				{
					$job = new Job($this->args('job_id'));
					if (!$job->isHydrated())
						throw new Exception("That job does not exist.");
					if ($job->get('user_id') != User::$me->id)
						throw new Exception("You do not own this job.");
					
					$file = $job->getSourceFile();
					  
					$queue_id = $job->get('queue_id');
				}
				else
					$file = new S3File($this->args('file_id'));

				if (!$file->isHydrated())
					throw new Exception("That file does not exist.");
				if ($file->get('user_id') != User::$me->id)
					throw new Exception("You do not have access to this file.");

				$this->set('file', $file);

				//load up our form.
				$form = $this->_createJobForm($file, $queue_id);
				if (isset($job))
					$form->action = "/job/create/job:{$job->id}";
				else
					$form->action = "/job/create/file:{$file->id}";
					
				//handle our form
				if ($form->checkSubmitAndValidate($this->args()))
				{
					//pull in our quantity
					$quantity = (int)$form->data('quantity');
					$quantity = max(1, $quantity);
					$quantity = min(1000, $quantity);
					
					//queue error checking.
					$queue = new Queue($form->data('queue_id'));
					if (!$queue->isHydrated())
						throw new Exception("That queue does not exist.");
					if (!$queue->canAdd())
						throw new Exception("You do not have permission to add to that queue.");
					
					//okay, we good?
					if ($file->isGCode())
					  $queue->addGCodeFile($file, $quantity);
					else if ($file->is3DModel())
					  $queue->add3DModelFile($file, $quantity);
          else
            throw new Exception("Oops, I don't know what type of file this is!");

          //let them know.
					Activity::log("added {$quantity} new " . Utility::pluralizeWord('job', $quantity));
						
					//send us ot the queue!
					$this->forwardToUrl($queue->getUrl());
				}
				
				$this->set('form', $form);
			}
			catch (Exception $e)
			{
				$this->set('megaerror', $e->getMessage());
			}
		}
		
		private function _createJobForm($file, $queue_id)
		{
			//load up our queues.
			$queues = User::$me->getQueues()->getAll();
			foreach ($queues AS $row)
			{
				$q = $row['Queue'];
				$qs[$q->id] = $q->getName();
			}

			$form = new Form();
			
			$form->add(new DisplayField(array(
				'label' => 'File',
				'help' => 'The file that will be printed.',
				'value' => $file->getLink()
			)));
			
			$form->add(new SelectField(array(
				'name' => 'queue_id',
				'label' => 'Queue',
				'help' => 'Which queue are you adding this job to?',
				'required' => true,
				'options' => $qs,
				'value' => $queue_id
			)));		
			
			$form->add(new TextField(array(
				'name' => 'quantity',
				'label' => 'Quantity',
				'help' => 'How many copies? Minimum 1, Maximum 100',
				'required' => true,
				'value' => 1
			)));
		
			return $form;
		}

		public function render_frame()
		{
		  $this->assertLoggedIn();
		  
		  try
		  {
  		  $file = new S3File($this->args('id'));
  		  if (!$file->isHydrated())
  		    throw new Exception("This file does not exist.");
  		    
  		  if ($file->get('user_id') != User::$me->id)
  		    throw new Exception("This is not your file.");
  		    
  		  $this->set('file', $file);
  		    
        if ($this->args('width'))
          $this->setArg('width');
        else
          $this->set('width', '100%');

        if ($this->args('height'))
          $this->setArg('height');
        else
          $this->set('height', '100%');
		  }
		  catch (Exception $e)
		  {
		    $this->set('megaerror', $e->getMessage());
		  }
		}
		
		function render_model()
		{
		  $this->setArg('file');
		  $this->setArg('width');
		  $this->setArg('height');
		}
		
		function render_gcode()
		{
		  $this->setArg('file');
		  $this->setArg('width');
		  $this->setArg('height');
		}
		
		public function file_jobs()
		{
			$this->assertLoggedIn();
			
			try
			{
				//did we get a queue?
				$file = new S3File($this->args('id'));
				if (!$file->isHydrated())
					throw new Exception("Could not find that queue.");
				if ($file->get('user_id') != User::$me->id)
					throw new Exception("You do not have permission to view this file.");
				
				$this->set('file', $file);

				//what sort of jobs to view?
				$this->setTitle($file->getLink() . " Jobs");
				$collection = $file->getJobs();
				
	      $per_page = 20;
	      $page = $collection->putWithinBounds($this->args('page'), $per_page);
    
	      $this->set('per_page', $per_page);
	      $this->set('total', $collection->count());
	      $this->set('page', $page);
	      $this->set('jobs', $collection->getPage($page, $per_page));	
			}
			catch (Exception $e)
			{
				$this->setTitle('File Jobs - Error');
				$this->set('megaerror', $e->getMessage());
			}
		}
	}
?>