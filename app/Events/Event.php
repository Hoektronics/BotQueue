<?php


namespace App\Events;


use App\Bot;
use App\Host;
use App\Job;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Collection;

class Event
{
    /**
     * @var Collection
     */
    private $channels;

    protected function addChannel(Channel $channel)
    {
        if($this->channels == null) {
            $this->channels = collect();
        }

        $this->channels->push($channel);

        return $this;
    }

    protected function channels()
    {
        return $this->channels->unique(function ($channel) {
            /** @var Channel $channel */
            return $channel->name;
        })->all();
    }

    /**
     * @param $user User|mixed
     * @return Event
     */
    protected function userChannel($user)
    {
        $user_id = $user;
        if ($user instanceof User) {
            $user_id = $user->id;
        }

        if($user_id !== null)
            return $this->addChannel(new PrivateChannel('user.'.$user_id));

        return $this;
    }

    /**
     * @param $bot Bot|mixed
     * @return Event
     */
    protected function botChannel($bot)
    {
        $bot_id = $bot;
        if ($bot instanceof Bot) {
            $bot_id = $bot->id;
        }

        if($bot_id !== null)
            return $this->addChannel(new PrivateChannel('bot.'.$bot_id));

        return $this;
    }

    /**
     * @param $job Bot|mixed
     * @return Event
     */
    protected function jobChannel($job)
    {
        $job_id = $job;
        if ($job instanceof Job) {
            $job_id = $job->id;
        }

        if($job_id !== null)
            return $this->addChannel(new PrivateChannel('job.'.$job_id));

        return $this;
    }

    /**
     * @param $host Bot|mixed
     * @return Event
     */
    protected function hostChannel($host)
    {
        $host_id = $host;
        if ($host instanceof Host) {
            $host_id = $host->id;
        }

        if($host_id !== null)
            return $this->addChannel(new PrivateChannel('host.'.$host_id));

        return $this;
    }
}