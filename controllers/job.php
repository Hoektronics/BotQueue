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
		$this->set('area', 'jobs');

		$available = User::$me->getJobs('available');
		$this->set('available', $available->getRange(0, 10));
		$this->set('available_count', $available->count());

		$taken = User::$me->getJobs('taken');
		$this->set('taken', $taken->getRange(0, 10));
		$this->set('taken_count', $taken->count());

		$complete = User::$me->getJobs('complete', 'finished_time', 'DESC');
		$this->set('complete', $complete->getRange(0, 10));
		$this->set('complete_count', $complete->count());

		$failure = User::$me->getJobs('failure');
		$this->set('failure', $failure->getRange(0, 10));
		$this->set('failure_count', $failure->count());
	}

	public function pretty()
	{
		$this->assertAdmin();

		$this->setTitle("Latest Completed Jobs");

		//$available = User::$me->getJobs('complete', 'finished_time', 'DESC');
		$sql = "SELECT id, webcam_image_id FROM jobs WHERE webcam_image_id != 0 AND status = 'complete' ORDER BY finished_time DESC";
		$available = new Collection($sql, array('Job' => 'id', 'S3File' => 'webcam_image_id'));
		$this->set('jobs', $available->getRange(0, 24));
	}

	public function listjobs()
	{
		$this->assertLoggedIn();

		$status = $this->args('status');
		$this->set('area', 'jobs');

		try {
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

			if ($status == 'complete')
				$collection = User::$me->getJobs($status, 'finished_time', 'DESC');
			else
				$collection = User::$me->getJobs($status);
			$per_page = 20;
			$page = $collection->putWithinBounds($this->args('page'), $per_page);

			$this->set('per_page', $per_page);
			$this->set('total', $collection->count());
			$this->set('page', $page);
			$this->set('jobs', $collection->getPage($page, $per_page));
			$this->set('status', $status);
		} catch (Exception $e) {
			$this->setTitle('View Jobs - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function view()
	{
		$this->assertLoggedIn();
		$this->set('area', 'jobs');

		try {
			//how do we find them?
			if ($this->args('id'))
				$job = new Job($this->args('id'));
			else
				throw new Exception("Could not find that job");

			//did we really get someone?
			if (!$job->canView())
				throw new Exception("You do not have permission to view this job.");
			if (!$job->isHydrated())
				throw new Exception("Could not find that job.");

			$this->setTitle('View Job - ' . $job->getName());

			//errors?
			if (!$this->get('megaerror')) {
				$this->setTitle('View Job - ' . $job->getName());

				$this->set('job', $job);
				$this->set('gcode_file', $job->getFile());
				$sf = $job->getSourceFile();
				$this->set('source_file', $sf);
				$this->set('parent_file', $sf->getParent());
				$this->set('slicejob', $job->getSliceJob());
				/* @var $sliceJob SliceJob */
				$sliceJob = $this->get('slicejob');
				$this->set('sliceengine', $sliceJob->getSliceEngine());
				$this->set('sliceconfig', $sliceJob->getSliceConfig());
				$this->set('queue', $job->getQueue());
				$this->set('bot', $job->getBot());
				$this->set('creator', $job->getUser());
				$this->set('errors', $job->getErrorLog()->getAll());
				$this->set('comments', $job->getComments()->getAll());
				$this->set('webcam', $job->getWebcamImage());
			}
		} catch (Exception $e) {
			$this->setTitle('View Job - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function edit()
	{
		$this->assertLoggedIn();
		$this->set('area', 'jobs');

		try {
			//how do we find them?
			if ($this->args('id'))
				$job = new Job($this->args('id'));
			else
				throw new Exception("Could not find that job");

			//did we really get someone?
			if (!$job->isHydrated())
				throw new Exception("Could not find that job.");
			if (!$job->canEdit())
				throw new Exception("You do not have permission to edit this job.");
			if ($job->get('status') != 'available')
				throw new Exception("You can only edit jobs that have not been taken yet.");

			$this->setTitle('Edit Job - ' . $job->getName());
			$this->set('job', $job);

			//load up our form.
			$form = $this->_createEditForm($job);
			$form->action = $job->getUrl() . "/edit";

			//handle our form
			if ($form->checkSubmitAndValidate($this->args())) {
				$queue = new Queue($form->data('queue_id'));
				if (!$queue->isMine())
					throw new Exception("That is not a valid queue.");

				$job->set('queue_id', $queue->id);
				$job->setName($form->data('name'));
				$job->save();

				Activity::log("edited the job " . $job->getLink() . ".");

				$this->forwardToUrl($job->getUrl());
			}

			$this->set('form', $form);
		} catch (Exception $e) {
			$this->setTitle('Edit Job - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	/**
	 * @param $job Job
	 * @return Form
	 */
	public function _createEditForm($job)
	{
		//load up our queues.
		$queues = User::$me->getQueues()->getAll();
		$qs = array();
		foreach ($queues AS $row) {
			/* @var $q Queue */
			$q = $row['Queue'];
			$qs[$q->id] = $q->getName();
		}

		$form = new Form();

		$form->add(new TextField(array(
			'name' => 'name',
			'label' => 'Job Name',
			'help' => 'What should we call this job?',
			'required' => true,
			'value' => $job->getName()
		)));

		$form->add(new SelectField(array(
			'name' => 'queue_id',
			'label' => 'Queue',
			'help' => 'Which queue does this bot pull jobs from?',
			'required' => true,
			'value' => $job->get('queue_id'),
			'options' => $qs
		)));

		return $form;
	}

	public function delete()
	{
		$this->assertLoggedIn();
		$this->set('area', 'jobs');

		try {
			//how do we find them?
			if ($this->args('id'))
				$job = new Job($this->args('id'));
			else
				throw new Exception("Could not find that job");

			//did we really get someone?
			if (!$job->isHydrated())
				throw new Exception("Could not find that job.");
			if (!$job->canEdit())
				throw new Exception("You do not have permission to delete this job.");
			if ($job->get('status') == 'taken')
				throw new Exception("You cannot delete jobs that are in progress from the web.  Cancel it from the client software instead.");
			if ($job->get('status') == 'slicing')
				throw new Exception("You cannot delete jobs that are in progress from the web.  Cancel it from the client software instead.");

			$this->set('job', $job);
			$this->setTitle('Delete Job - ' . $job->getName());

			if ($this->args('submit')) {
				Activity::log("deleted the job <strong>" . $job->getName() . "</strong>.");

				$job->delete();

				$this->forwardToUrl("/jobs");
			}
		} catch (Exception $e) {
			$this->setTitle('Delete Job - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function cancel()
	{
		$this->assertLoggedIn();
		$this->set('area', 'jobs');

		try {
			//how do we find them?
			if ($this->args('id'))
				$job = new Job($this->args('id'));
			else
				throw new Exception("Could not find that job");

			//did we really get someone?
			if (!$job->isHydrated())
				throw new Exception("Could not find that job.");
			if (!$job->canEdit())
				throw new Exception("You do not have permission to cancel this job.");
			// if ($job->get('status') == 'taken')
			//  throw new Exception("You cannot delete jobs that are in progress from the web.  Cancel it from the client software instead.");
			// if ($job->get('status') == 'slicing')
			//  throw new Exception("You cannot delete jobs that are in progress from the web.  Cancel it from the client software instead.");

			$this->set('job', $job);
			$this->setTitle('Cancel Job - ' . $job->getName());

			if ($this->args('submit')) {
				Activity::log("cancelled the job <strong>" . $job->getName() . "</strong>.");

				$job->cancelJob();

				$this->forwardToUrl($job->getUrl());
			}
		} catch (Exception $e) {
			$this->setTitle('Cancel Job - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function bump()
	{
		$this->assertLoggedIn();
		$this->set('area', 'jobs');

		try {
			//how do we find them?
			if ($this->args('id'))
				$job = new Job($this->args('id'));
			else
				throw new Exception("Could not find that job");

			//did we really get someone?
			if (!$job->isHydrated())
				throw new Exception("Could not find that job.");
			if (!$job->canEdit())
				throw new Exception("You do not have permission to bump this job.");
			// if ($job->get('status') == 'taken')
			//  throw new Exception("You cannot delete jobs that are in progress from the web.  Cancel it from the client software instead.");
			// if ($job->get('status') == 'slicing')
			//  throw new Exception("You cannot delete jobs that are in progress from the web.  Cancel it from the client software instead.");

			$this->set('job', $job);
			$this->setTitle('Bump Job - ' . $job->getName());

			$job->pushToTop();
			$this->forwardToUrl("/");
		} catch (Exception $e) {
			$this->setTitle('Bump Job - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function qa()
	{
		$this->assertLoggedIn();
		$this->set('area', 'jobs');

		try {
			//how do we find them?
			if ($this->args('id'))
				$job = new Job($this->args('id'));
			else
				throw new Exception("Could not find that job");

			//did we really get someone?
			if (!$job->isHydrated())
				throw new Exception("Could not find that job.");
			if (!$job->canEdit())
				throw new Exception("You do not have permission to edit this job.");
			if ($job->get('status') != 'qa')
				throw new Exception("You cannot do QA on this job.");

			$bot = $job->getBot();

			$this->set('job', $job);
			$this->set('bot', $bot);
			$this->set('gcode_file', $job->getFile());
			$this->set('source_file', $job->getSourceFile());
			$this->set('webcam', $job->getWebcamImage());

			$this->setTitle('Verify Job - ' . $job->getName());

			$form = $this->_createQAFailForm($job);
			$this->set('form', $form);
		} catch (Exception $e) {
			$this->setTitle('Delete Job - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function qa_pass()
	{
		$this->assertLoggedIn();
		$this->set('area', 'jobs');

		try {
			//how do we find them?
			if ($this->args('id'))
				$job = new Job($this->args('id'));
			else
				throw new Exception("Could not find that job");

			//did we really get someone?
			if (!$job->isHydrated())
				throw new Exception("Could not find that job.");
			if (!$job->canEdit())
				throw new Exception("You do not have permission to edit this job.");
			if ($job->get('status') != 'qa')
				throw new Exception("You cannot do QA on this job.");

			$bot = $job->getBot();

			$this->set('job', $job);
			$this->set('bot', $bot);

			$bot->set('job_id', 0);
			$bot->setStatus(BotState::Idle);
			$bot->save();

			$job->setStatus('complete');
			$job->set('verified_time', date("Y-m-d H:i:s"));
			$job->save();

			Activity::log("accepted the output of job " . $job->getLink() . ".");

			$this->forwardToUrl("/");
		} catch (Exception $e) {
			$this->setTitle('Accept Job - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function qa_fail()
	{
		$this->assertLoggedIn();
		$this->set('area', 'jobs');

		try {
			//how do we find them?
			if ($this->args('id'))
				$job = new Job($this->args('id'));
			else
				throw new Exception("Could not find that job");

			//did we really get someone?
			if (!$job->isHydrated())
				throw new Exception("Could not find that job.");
			if (!$job->canEdit())
				throw new Exception("You do not have permission to edit this job.");
			if ($job->get('status') != 'qa')
				throw new Exception("You cannot do QA on this job.");

			$this->setTitle("Fail Job - " . $job->getName());
			$bot = $job->getBot();

			$this->set('job', $job);
			$this->set('bot', $bot);

			$form = $this->_createQAFailForm($job);
			$this->set('form', $form);

			if ($form->checkSubmitAndValidate($this->args())) {
				if ($form->data('failure_reason') == 'Other')
					$error_text = $form->data('failure_reason_other');
				else
					$error_text = $form->data('failure_reason');

				//log that shit!
				$job->logError($error_text);

				if ($form->data('bot_error')) {
					$bot->set('job_id', 0);
					$bot->setStatus(BotState::Error);
					$bot->set('error_text', $error_text);
					$bot->save();

					Activity::log("took the bot " . $bot->getLink() . "offline for repairs.");
				} else {
					$bot->set('job_id', 0);
					$bot->setStatus(BotState::Idle);
					$bot->save();
				}

				if ($form->data('job_error')) {
					$job->setStatus('failure');
					$job->set('verified_time', date("Y-m-d H:i:s"));
					$job->save();
				} else {
					$job->setStatus('available');
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
		} catch (Exception $e) {
			$this->setTitle('Fail Job - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	/**
	 * @param $job Job
	 * @return Form
	 */
	public function _createQAFailForm($job)
	{
		$form = new Form();
		$form->action = $job->getUrl() . "/qa/fail";

		$failure_options = array(
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
			'options' => $failure_options
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
		$this->set('area', 'jobs');

		try {
			//how do we find them?
			if ($this->args('id'))
				$file = new S3File($this->args('id'));
			else
				throw new Exception("Could not find that file");

			//did we really get someone?
			if (!$file->isHydrated())
				throw new Exception("Could not find that file.");
			if ($file->get('user_id') != User::$me->id)
				throw new Exception("You do not own that file.");

			$this->setTitle($file->getName());

			$this->set('file', $file);
			$this->set('creator', $file->getUser());
			$this->set('parent_file', $file->getParent());
			$this->set('kids', $file->getChildren()->getAll());

			$jobs = $file->getJobs();
			$this->set('jobs', $jobs->getRange(0, 10));
			$this->set('job_count', $jobs->count());
		} catch (Exception $e) {
			$this->setTitle('View File - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}


	public function passthru()
	{
		$this->assertLoggedIn();

		try {
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
			header('Content-Disposition: attachment; filename=' . basename($file->getRealUrl()));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . (int)$file->get('size'));

			//kay, send it
			readfile($file->getRealUrl());
			exit;
		} catch (Exception $e) {
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

	public function draw_on_deck_jobs()
	{
		$this->setArg('jobs');
	}

	public function draw_finished_jobs()
	{
		$this->setArg('jobs');
	}

	public function create()
	{
		$this->assertLoggedIn();
		$this->set('area', 'jobs');

		try {
			if ($this->args('job_id')) {
				$job = new Job($this->args('job_id'));
				if (!$job->isHydrated())
					throw new Exception("That job does not exist.");
				if ($job->get('user_id') != User::$me->id)
					throw new Exception("You do not own this job.");

				$file = $job->getSourceFile();
				$queue_id = $job->get('queue_id');
			} else if($this->args('file_id')) {
				$file = new S3File($this->args('file_id'));
				$queue_id = User::$me->getDefaultQueue()->id;
			} else {
				throw new Exception("Could not create that file");
			}

			if (!$file->isHydrated())
				throw new Exception("That file does not exist.");
			if ($file->get('user_id') != User::$me->id)
				throw new Exception("You do not have access to this file.");

			$this->setTitle('Create New Job - ' . $file->getLink());
			$this->set('file', $file);

			$kids = $file->getChildren()->getAll();
			if (!empty($kids)) {
				$this->set('kids', $kids);
				$queues = User::$me->getQueues()->getAll();
				$this->set('queues', $queues);

				if ($this->args('submit')) {
					$use = $this->args('use');
					$qty = $this->args('qty');
					$queues = $this->args('queues');
					$priority = $this->args('priority');

					// echo "<pre>";
					// var_dump($this->args());
					// var_dump($qty);
					// var_dump($queues);
					// var_dump($priority);
					// echo "</pre>";

					//what ones do we want to actually add?
					foreach ($use AS $id => $value) {
						$kid = new S3File($id);
						if (!$kid->isHydrated())
							throw new Exception("That file does not exist.");
						if ($kid->get('user_id') != User::$me->id)
							throw new Exception("You do not have access to this file.");

						$this->_createJobsFromFile($kid, (int)$qty[$id], (int)$queues[$id], (bool)$priority[$id]);
					}

					$this->forwardToUrl('/');
				}
			} else {
				//load up our form.
				$form = $this->_createJobForm($file, $queue_id);
				if (isset($job))
					$form->action = "/job/create/job:{$job->id}";
				else
					$form->action = "/job/create/file:{$file->id}";

				//handle our form
				if ($form->checkSubmitAndValidate($this->args())) {
					//make our jobs!
					$this->_createJobsFromFile($file, $form->data('quantity'), $form->data('queue_id'), $form->data('priority'));
					$quantity = $form->data('quantity');

					//let them know.
					Activity::log("added {$quantity} new " . Utility::pluralizeWord('job', $quantity));

					//send us to the dashboard!
					$this->forwardToUrl("/");
				}

				$this->set('form', $form);
			}
		} catch (Exception $e) {
			$this->set('megaerror', $e->getMessage());
		}
	}

	/**
	 * @param $file S3File
	 * @param $quantity mixed
	 * @param $queue_id mixed
	 * @param $priority mixed
	 * @return array
	 * @throws Exception
	 */
	private function _createJobsFromFile($file, $quantity, $queue_id, $priority)
	{
		//pull in our quantity
		$quantity = (int)$quantity;
		$quantity = max(1, $quantity);
		$quantity = min(1000, $quantity);

		//queue error checking.
		$queue = new Queue($queue_id);
		if (!$queue->isHydrated())
			throw new Exception("That queue does not exist.");
		if (!$queue->isMine())
			throw new Exception("You do not have permission to add to that queue.");

		//okay, we good?
		$jobs = $queue->addFile($file, $quantity);

		//priority or not?
		if ($priority)
			if (!empty($jobs))
				foreach ($jobs AS $job)
					/* @var $job Job */
					$job->pushToTop();

		return $jobs;
	}

	/**
	 * @param $file S3File
	 * @param $queue_id int
	 * @return Form
	 */
	private function _createJobForm($file, $queue_id)
	{
		//load up our queues.
		$queues = User::$me->getQueues()->getAll();
		$qs = array();
		foreach ($queues AS $row) {
			/* @var $q Queue */
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

		$form->add(new CheckboxField(array(
			'name' => 'priority',
			'label' => 'Is this a priority job?',
			'help' => 'Check this box to push this job to the top of the queue.'
		)));

		return $form;
	}

	public function render_frame()
	{
		$this->assertLoggedIn();

		try {
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
		} catch (Exception $e) {
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
		$this->set('area', 'jobs');

		try {
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
		} catch (Exception $e) {
			$this->setTitle('File Jobs - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}
}

?>