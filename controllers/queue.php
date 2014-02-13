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

class QueueController extends Controller
{
    public function home()
    {
        $this->assertLoggedIn();

        $this->setTitle(User::$me->getName() . "'s Queues");
        $this->set('area', 'queues');

        $collection = User::$me->getQueues();
        $per_page = 20;
        $page = $collection->putWithinBounds($this->args('page'), $per_page);

        $this->set('per_page', $per_page);
        $this->set('total', $collection->count());
        $this->set('page', $page);
        $this->set('queues', $collection->getPage($page, $per_page));
    }

    public function create()
    {
        $this->assertLoggedIn();

        $this->setTitle('Create a New Queue');
        $this->set('area', 'queues');

        if ($this->args('submit')) {
            //did we get a name?
            if (!$this->args('name')) {
                $errors['name'] = "You need to provide a name.";
                $errorfields['name'] = 'error';
            }

            //okay, we good?
            if (empty($errors) && empty($errorfields)) {
                //woot!
                $q = new Queue();
                $q->set('name', $this->args('name'));
                $q->set('user_id', User::$me->id);
                $q->save();

                //todo: send a confirmation email.
                Activity::log("created a new queue named " . $q->getLink() . ".");

                $this->forwardToUrl($q->getUrl());
            } else {
                $this->set('errors', $errors);
                $this->set('errorfields', $errorfields);
                $this->setArg('name');
            }
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

    //TODO refactor this function
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
        $jids = array();
        foreach ($jobs AS $job) {
            $jarray = explode("_", $job);
            $jid = (int)$jarray[1];
            if (!$jid)
                die("Error: format must be a csv of job_### where ### is the job id.");
            $jids[] = (int)$jid;
        }

        //find our our current max
        $sql = "SELECT min(user_sort) FROM jobs WHERE id IN (" .
            db()->escape(implode($jids, ",")) .
            ")";
        $min = (int)db()->getValue($sql);

        //now actually update.
        foreach ($jids AS $jid) {
            $job = new Job($jid);
            if ($job->get('user_id') == User::$me->id) {
                $job->set('user_sort', $min);
                $job->save();
                $min++;
            } else
                die("Error: Job {$jid} is not your job.");
        }

        die("OK");
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
            if ($status == 'available') {
                $this->setTitle($queue->getName() . "'s Available Jobs");
                $collection = $queue->getJobs($status);
            } else if ($status == 'taken') {
                $this->setTitle($queue->getName() . "'s Working Jobs");
                $collection = $queue->getJobs($status);
            } else if ($status == 'complete') {
                $this->setTitle($queue->getName() . "'s Finished Jobs");
                $collection = $queue->getJobs($status);
            } else if ($status == 'failure') {
                $this->setTitle($queue->getName() . "'s Failed Jobs");
                $collection = $queue->getJobs($status);
            } else if ($status == 'all') {
                $this->setTitle($queue->getName() . "'s Jobs");
                $collection = $queue->getJobs(null);
            } else
                throw new Exception("That is not a valid status!");

            $per_page = 20;
            $page = $collection->putWithinBounds($this->args('page'), $per_page);

            $this->set('per_page', $per_page);
            $this->set('total', $collection->count());
            $this->set('page', $page);
            $this->set('jobs', $collection->getPage($page, $per_page));
            $this->set('status', $status);
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
            if ($queue->get('user_id') != User::$me->id)
                throw new Exception("You do not own this queue.");

            $this->set('queue', $queue);
            $this->setTitle('Edit Queue - ' . $queue->getName());

            //load up our form.
            $form = $this->_createQueueForm($queue);
            $form->action = $queue->getUrl() . "/edit";

            //handle our form
            if ($form->checkSubmitAndValidate($this->args())) {
                $queue->set('name', $form->data('name'));
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

        $form->add(new TextField(array(
            'name' => 'name',
            'label' => 'Name',
            'help' => 'What is the name of this queue?',
            'required' => true,
            'value' => $queue->get('name')
        )));

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

?>
