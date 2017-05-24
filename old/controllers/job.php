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

class JobController extends Controller
{
	public function home()
	{
		$this->assertLoggedIn();

		$this->setTitle(User::$me->getName() . "'s Jobs");
		$this->set('area', 'jobs');

		$available = User::$me->getJobs(JobState::Available);
		$this->set('available', $available->getRange(0, 10));
		$this->set('available_count', $available->count());

		$taken = User::$me->getJobs(JobState::Taken);
		$this->set('taken', $taken->getRange(0, 10));
		$this->set('taken_count', $taken->count());

		$complete = User::$me->getJobs(JobState::Complete, 'finished_time', 'DESC');
		$this->set('complete', $complete->getRange(0, 10));
		$this->set('complete_count', $complete->count());

		$failure = User::$me->getJobs(JobState::Failure);
		$this->set('failure', $failure->getRange(0, 10));
		$this->set('failure_count', $failure->count());
	}

	public function pretty()
	{
		$this->assertAdmin();

		$this->setTitle("Latest Completed Jobs");

		$sql = "SELECT id, webcam_image_id FROM jobs WHERE webcam_image_id != 0 AND status = 'complete' ORDER BY finished_time DESC";
		$available = new Collection($sql);
		$available->bindType('id', 'Job');
		$available->bindType('webcam_image_id', 'StorageInterface');

		$this->set('jobs', $available->getRange(0, 24));
	}

	public function listjobs()
	{
		$this->assertLoggedIn();

		$status = $this->args('status');
		$this->set('area', 'jobs');

		try {
			$titles = array(
				JobState::Available => 'Available',
				JobState::Taken => 'Working',
				JobState::Complete => 'Finished',
				JobState::Failure => 'Failed'
			);

			if(array_key_exists($status, $titles)) {
				$title = User::$me->getName() . "'s {$titles[$status]} Jobs";
				$this->setTitle($title);
			} else {
				throw new Exception("That is not a valid status!");
			}

			if ($status == JobState::Complete)
				$collection = User::$me->getJobs($status, 'finished_time', 'DESC');
			else
				$collection = User::$me->getJobs($status);

			$this->set('jobs',
				$collection->getPage(
					$this->args('page'),
					20
				)
			);
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
			$job = $this->getJobByID($this->args('id'));

			//did we really get someone?
			$this->ensureJobExists($job);
			if (!$job->canView())
				throw new Exception("You do not have permission to view this job.");

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
				$this->set('comment_count', $job->getComments()->count());
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
			$job = $this->getJobByID($this->args('id'));

			//did we really get someone?
			if (!$job->isHydrated())
				throw new Exception("Could not find that job.");
			if (!$job->canEdit())
				throw new Exception("You do not have permission to edit this job.");
			if ($job->get('status') != JobState::Available)
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

		$form->add(
			TextField::name('name')
				->label('Job Name')
				->help('What should we call this job?')
				->required(true)
				->value($job->getName())
		);

		$form->add(
			SelectField::name('queue_id')
				->label('Queue')
				->help('Which queue does this bot pull jobs from?')
				->required(true)
				->value($job->get('queue_id'))
				->options($qs)
		);

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

			$this->ensureJobExists($job);
			if (!$job->canEdit())
				throw new Exception("You do not have permission to delete this job.");
			if ($job->get('status') == JobState::Taken)
				throw new Exception("You cannot delete jobs that are in progress from the web.  Cancel it from the client software instead.");
			if ($job->get('status') == JobState::Slicing)
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
			$job = $this->getJobByID($this->args('id'));

			$this->ensureJobExists($job);
			if (!$job->canEdit())
				throw new Exception("You do not have permission to cancel this job.");

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
			$job = $this->getJobByID($this->args('id'));

			$this->ensureJobExists($job);

			if (!$job->canEdit())
				throw new Exception("You do not have permission to bump this job.");

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
			$job = $this->getJobByID($this->args('id'));

			$this->ensureJobExists($job);
			if (!$job->canEdit())
				throw new Exception("You do not have permission to edit this job.");
			if ($job->get('status') != JobState::QA)
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
			$job = $this->getJobByID($this->args('id'));

			$this->ensureJobExists($job);
			if (!$job->canEdit())
				throw new Exception("You do not have permission to edit this job.");

			if ($job->get('status') != JobState::QA)
				throw new Exception("You cannot do QA on this job.");

			$bot = $job->getBot();

			$this->set('job', $job);
			$this->set('bot', $bot);

			$bot->reset();

			$job->setStatus(JobState::Complete);
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
			$job = $this->getJobByID($this->args('id'));

			$this->ensureJobExists($job);
			if (!$job->canEdit())
				throw new Exception("You do not have permission to edit this job.");
			if ($job->get('status') != JobState::QA)
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
					$bot->setStatus(BotState::Error);
					$bot->set('error_text', $error_text);

					Activity::log("took the bot " . $bot->getLink() . "offline for repairs.");
				} else {
					$bot->setStatus(BotState::Idle);
				}

				$bot->set('job_id', 0);
				$bot->save();

				if ($form->data('job_error')) {
					$job->setStatus(JobState::Failure);
					$job->set('user_sort', 0);
					$job->set('verified_time', date("Y-m-d H:i:s"));
					$job->save();
				} else {
					$job->reset();
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

		$form->add(
			SelectField::name('failure_reason')
				->label('Reason for failure')
				->help('Please enter a reason for rejecting this print.')
				->required(true)
				->options($failure_options)
		);

		$form->add(
			TextField::name('failure_reason_other')
				->label('Other Reason')
				->help('If you selected "other" above, please enter the reason here.')
				->value("")
		);

		$form->add(
			CheckboxField::name('bot_error')
				->label('Put the bot in error/maintenance mode?')
				->help('Check this box if the bot needs maintenance and should stop grabbing jobs.')
				->value(1)
		);

		$form->add(
			CheckboxField::name('job_error')
				->label('Pull this job from the queue?')
				->help('Check this box if the job itself has issues and should be pulled from the queue.')
		);

		return $form;
	}

	public function file()
	{
		$this->set('area', 'jobs');

		try {
			//how do we find them?
			if ($this->args('id'))
				$file = Storage::get($this->args('id'));
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
			$file = Storage::get($this->args('id'));
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
			header('Content-Disposition: attachment; filename=' . basename($file->get('path')));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . (int)$file->get('size'));

			//kay, send it
			readfile($file->getDownloadURL());
			exit;
		} catch (Exception $e) {
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function draw_jobs()
	{
		$this->setArg('jobs');
	}

	public function draw_jobs_available()
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
		$this->set('area', 'jobs');

		try {
			if ($this->args('job_id')) {
				$job = new Job($this->args('job_id'));
				$this->ensureJobExists($job);

				if (!$job->isMine())
					throw new Exception("You do not own this job.");

				$file = $job->getSourceFile();
				$queue_id = $job->get('queue_id');
			} else if ($this->args('file_id')) {
				$file = Storage::get($this->args('file_id'));
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

					//what ones do we want to actually add?
					foreach ($use AS $id => $value) {
						$kid = Storage::get($id);
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
	 * @param $file StorageInterface
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
	 * @param $file StorageInterface
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

		$form->add(
			DisplayField::name('file_name')
				->label('File')
				->help('The file that will be printed.')
				->value($file->getLink())
		);

		$form->add(
			SelectField::name('queue_id')
				->label('Queue')
				->help('Which queue are you adding this job to?')
				->required(true)
				->options($qs)
				->value($queue_id)
		);

		$form->add(
			TextField::name('quantity')
				->label('Quantity')
				->help('How many copies? Minimum 1, Maximum 100')
				->required(true)
				->value(1)
		);

		$form->add(
			CheckboxField::name('priority')
				->label('Is this a priority job?')
				->help('Check this box to push this job to the top of the queue')
				->checked(false)
		);

		return $form;
	}

	public function render_frame()
	{
		$this->assertLoggedIn();

		try {
			$file = Storage::get($this->args('id'));
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
			$file = Storage::get($this->args('id'));
			if (!$file->isHydrated())
				throw new Exception("Could not find that queue.");
			if ($file->get('user_id') != User::$me->id)
				throw new Exception("You do not have permission to view this file.");

			$this->set('file', $file);

			//what sort of jobs to view?
			$this->setTitle($file->getLink() . " Jobs");
			$collection = $file->getJobs();

			$this->set('jobs',
				$collection->getPage(
					$this->args('page'),
					20
				)
			);
		} catch (Exception $e) {
			$this->setTitle('File Jobs - Error');
			$this->set('megaerror', $e->getMessage());
		}
	}

	/**
	 * @param Job $job
	 * @throws Exception
	 */
	private function ensureJobExists($job)
	{
		//did we really get someone?
		if (!$job->isHydrated())
			throw new Exception("Could not find that job.");
	}

	/**
	 * @param int $id
	 * @return Job
	 * @throws Exception
	 */
	private function getJobByID($id)
	{
		//how do we find them?
		if ($id) {
			$job = new Job($id);
			return $job;
		} else {
			throw new Exception("Could not find that job");
		}
	}
}
