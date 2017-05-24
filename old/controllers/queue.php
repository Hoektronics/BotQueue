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

class QueueController extends Controller
{
    public function home()
    {
        $this->assertLoggedIn();

        $this->setTitle(User::$me->getName() . "'s Queues");
        $this->set('area', 'queues');

        $collection = User::$me->getQueues();

        $this->set('queues',
            $collection->getPage(
                $this->args('page'),
                20
            )
        );
    }

    public function create()
    {
        $this->assertLoggedIn();

        $this->setTitle('Create a New Queue');
        $this->set('area', 'queues');

	    $queue = new Queue();
	    $form = $this->_createQueueForm($queue);
	    $this->set('form', $form);

        if ($form->checkSubmitAndValidate($this->args())) {
	        $queue->set('name', $this->args('name'));
	        $queue->set('user_id', User::$me->id);
	        $queue->set('delay', $this->args('delay'));
	        $queue->save();

            Activity::log("created a new queue named " . $queue->getLink() . ".");

	        $this->forwardToUrl($queue->getUrl());
        }
    }

    public function view()
    {
        $this->assertLoggedIn();
        $this->set('area', 'queues');

        try {
            //how do we find them?
            if ($this->args('id'))
                $q = new Queue($this->args('id'));
            else
                throw new Exception("Could not find that queue.");

            //did we really get someone?
            if (!$q->isHydrated())
                throw new Exception("Could not find that queue.");
            if (!$q->isMine())
                throw new Exception("You do not have permission to view this queue.");

            $this->setTitle("View Queue - " . $q->getName());
            $this->set('queue', $q);

            $available = $q->getJobs('available', 'user_sort', 'ASC');
            $this->set('available', $available->getRange(0, 20));
            $this->set('available_count', $available->count());

            $taken = $q->getJobs('taken', 'user_sort', 'DESC');
            $this->set('taken', $taken->getRange(0, 20));
            $this->set('taken_count', $taken->count());

            $complete = $q->getJobs('complete', 'user_sort', 'DESC');
            $this->set('complete', $complete->getRange(0, 20));
            $this->set('complete_count', $complete->count());

            $qa = $q->getJobs('qa', 'finished_time', 'DESC');
            $this->set('qa', $qa->getRange(0, 20));
            $this->set('qa_count', $qa->count());

            $failure = $q->getJobs('failure', 'user_sort', 'DESC');
            $this->set('failure', $failure->getRange(0, 20));
            $this->set('failure_count', $failure->count());

            $this->set('stats', QueueStats::getStats($q));

            $bots = $q->getBots();
            $this->set('bots', $bots->getRange(0, 20));
            $this->set('bot_count', $bots->count());

            $this->set('errors', $q->getErrorLog()->getRange(0, 50));
        } catch (Exception $e) {
            $this->setTitle('View Queue - Error');
            $this->set('megaerror', $e->getMessage());
        }
    }

    //ajax function
    public function update_sort()
    {
        $this->assertLoggedIn();

        if (!$this->args('jobs'))
            die("Error: You didn't pass any jobs in.");

        $jobs = explode(",", $this->args('jobs'));
        if (count($jobs) < 1)
            die("Error: You need to pass in at least 2 jobs.");

        //load up our ids.
        $jobIds = preg_filter('/^job_(\d+)$/', '$1', $jobs);
        $jobs = array();

        // Only grab jobs that exist and are ours
        foreach($jobIds AS $id) {
            /** @var Job $job */
            $job = new Job($id);
            if($job->isHydrated() && $job->isMine()) {
                $jobs[$id] = $job->get('user_sort');
            }
        }

        // Sort the values, but not the keys
        $values = array_values($jobs);
        sort($values);
        $jobs = array_combine(array_keys($jobs), $values);

        // Now actually update
        foreach($jobs AS $id => $sort) {
            /** @var Job $job */
            $job = new Job($id);
            $job->set('user_sort', $sort);
            $job->save();
        }

        die(print_r($jobs, true));
    }

    public function listjobs()
    {
        $this->assertLoggedIn();
        $this->set('area', 'queues');

        try {
            //did we get a queue?
            $queue = new Queue($this->args('id'));
            if (!$queue->isHydrated())
                throw new Exception("Could not find that queue.");
            if (!$queue->isMine())
                throw new Exception("You do not have permission to view this queue.");
            $this->set('queue', $queue);

            //what sort of jobs to view?
            $status = $this->args('status');
            $titles = array(
                JobState::Available => 'Available',
                JobState::Taken => 'Working',
                JobState::Complete => 'Finished',
                JobState::Failure => 'Failed'
            );

            if(array_key_exists($status, $titles)) {
                $title = $queue->getName() . "'s {$titles[$status]} Jobs";
                $collection = $queue->getJobs($status);
            } else if($status == 'all') {
                $title = $queue->getName() . "'s Jobs";
                $collection = $queue->getJobs();
            } else {
                throw new Exception("That is not a valid status!");
            }
            $this->setTitle($title);

            $this->set('status', $status);
            $this->set('jobs',
                $collection->getPage(
                    $this->args('page'),
                    20
                )
            );
        } catch (Exception $e) {
            $this->setTitle('View Queue - Error');
            $this->set('megaerror', $e->getMessage());
        }
    }

    public function draw_queues()
    {
        $this->setArg('queues');
    }

    public function edit()
    {
        $this->assertLoggedIn();
        $this->set('area', 'queues');

        try {
            //how do we find them?
            $queue = new Queue($this->args('id'));

            //did we really get someone?
            if (!$queue->isHydrated())
                throw new Exception("Could not find that queue.");
            if (!$queue->isMine())
                throw new Exception("You do not own this queue.");

            $this->set('queue', $queue);
            $this->setTitle('Edit Queue - ' . $queue->getName());

            //load up our form.
            $form = $this->_createQueueForm($queue);
            $form->action = $queue->getUrl() . "/edit";

            //handle our form
            if ($form->checkSubmitAndValidate($this->args())) {
                $queue->set('name', $this->args('name'));
	            $queue->set('delay', $this->args('delay'));
                $queue->save();

                Activity::log("edited the queue " . $queue->getLink() . ".");

                $this->forwardToUrl($queue->getUrl());
            }

            $this->set('form', $form);
        } catch (Exception $e) {
            $this->setTitle('Edit Queue - Error');
            $this->set('megaerror', $e->getMessage());
        }
    }

    /**
     * @param $queue Queue
     * @return Form
     */
    public function _createQueueForm($queue)
    {
        $form = new Form();

		$form->add(
			TextField::name('name')
			->label('Name')
			->help('What is the name of this queue?')
			->required(true)
			->value($queue->getName())
		);

	    $form->add(
		    NumericField::name('delay')
		    ->label('Delay')
		    ->help('What is the delay between starting prints in seconds?')
		    ->required(true)
			->min(0)
		    ->value($queue->get('delay'))
	    );

        return $form;
    }

    public function delete()
    {
        $this->assertLoggedIn();
        $this->set('area', 'queues');

        try {
            //how do we find them?
            $queue = new Queue($this->args('id'));

            //did we really get someone?
            if (!$queue->isHydrated())
                throw new Exception("Could not find that queue.");
            if ($queue->get('user_id') != User::$me->id)
                throw new Exception("You do not own this queue.");
            if (User::$me->getQueues()->count() == 1)
                throw new Exception("You cannot delete your last queue. Create a new one before deleting this one.");

            $this->set('queue', $queue);
            $this->setTitle('Delete Queue - ' . $queue->getName());

            if ($this->args('submit')) {
                Activity::log("deleted the queue <strong>" . $queue->getName() . "</strong>.");

                $queue->delete();

                $this->forwardToUrl("/");
            }
        } catch (Exception $e) {
            $this->setTitle('Delete Queue - Error');
            $this->set('megaerror', $e->getMessage());
        }
    }

    public function flush()
    {
        $this->assertLoggedIn();
        $this->set('area', 'queues');

        try {
            //how do we find them?
            $queue = new Queue($this->args('id'));

            //did we really get someone?
            if (!$queue->isHydrated())
                throw new Exception("Could not find that queue.");
            if ($queue->get('user_id') != User::$me->id)
                throw new Exception("You do not own this queue.");

            $this->set('queue', $queue);
            $this->setTitle('Empty Queue - ' . $queue->getName());

            if ($this->args('submit')) {
                Activity::log("emptied the queue <strong>" . $queue->getName() . "</strong>.");

                $queue->flush();

                $this->forwardToUrl($queue->getUrl());
            }
        } catch (Exception $e) {
            $this->setTitle('Empty Queue - Error');
            $this->set('megaerror', $e->getMessage());
        }
    }
}