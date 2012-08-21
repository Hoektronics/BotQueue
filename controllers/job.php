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
				$this->set('megaerror', $e->getMessage());
			}
		}

		public function view()
		{
			//how do we find them?
			if ($this->args('id'))
				$job = new Job($this->args('id'));

			//did we really get someone?
			if (!$job->isHydrated())
				$this->set('megaerror', "Could not find that job.");

			$this->setTitle('View Job - ' . $job->getName());
				
			//errors?
			if (!$this->get('megaerror'))
			{
				$this->setTitle('View Job - ' . $job->getName());

				$this->set('job', $job);
				$this->set('file', $job->getFile());
				$this->set('queue', $job->getQueue());
				$this->set('bot', $job->getBot());
				$this->set('creator', $job->getUser());
			}
		}

		public function edit()
		{
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
				$this->setTitle('View Job - Error');
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
			
				$this->setTitle('View File - ' . $file->getName());

				$this->set('file', $file);
				$this->set('creator', $file->getUser());
			}
			catch (Exception $e)
			{
				$this->setTitle('View File - Error');
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
					
					$file = $job->getFile();
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
					$queue->addGCodeFile($file, $quantity);
					Activity::log("added {$quantity} new " . Utility::pluralizeWord('job', $quantity));
						
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
	}
?>
