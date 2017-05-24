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

class JobClockEntry extends Model
{
    public function __construct($id = null)
    {
        parent::__construct($id, "job_clock");
    }

    public function getJob()
    {
        return new Job($this->get('job_id'));
    }

    public function getBot()
    {
        return new Job($this->get('bot_id'));
    }

    public function getQueue()
    {
        return new Job($this->get('queue_id'));
    }

    public function setStatus($status) {
        $this->set('status', $status);
    }

    public function getAPIData()
    {
        $d = array();
        $d['id'] = $this->id;
        $d['job_id'] = $this->get('job_id');
        $d['bot_id'] = $this->get('bot_id');
        $d['queue_id'] = $this->get('queue_id');
        $d['status'] = $this->get('status');
        $d['created_time'] = $this->get('start_date');
        $d['taken_time'] = $this->get('end_date');

        return $d;
    }

    public function getElapsedTime()
    {
        if ($this->get('status') == 'working') {
            $start = strtotime($this->get('start_date'));
            $end = time();
        } else {
            $start = strtotime($this->get('start_date'));
            $end = strtotime($this->get('end_date'));
        }

        return $end - $start;
    }

    public function getElapsedText()
    {
        return Utility::getElapsed($this->getElapsedTime());
    }
}

?>